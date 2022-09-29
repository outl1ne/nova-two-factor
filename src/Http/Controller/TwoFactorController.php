<?php

namespace Outl1ne\NovaTwoFactor\Http\Controller;


use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Outl1ne\NovaTwoFactor\Models\TwoFa;
use Outl1ne\NovaTwoFactor\TwoFaAuthenticator;

class TwoFactorController extends Controller
{
    private $novaGuard;

    public function __construct()
    {
        $this->novaGuard = config('nova.guard', 'web');
    }

    public function registerUser()
    {

        if (auth($this->novaGuard)->user()->twoFa && auth($this->novaGuard)->user()->twoFa->confirmed == 1) {
            return response()->json([
                'message' => __('Already verified !')
            ]);
        }

        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();

        $recoveryKey = strtoupper(Str::random(16));
        $recoveryKey = str_split($recoveryKey, 4);
        $recoveryKey = implode("-", $recoveryKey);

        $recoveryKeyHashed = bcrypt($recoveryKey);

        $data['recovery'] = $recoveryKey;


        $userTwoFa = new TwoFa();
        $userTwoFa::where('user_id', auth($this->novaGuard)->user()->id)->delete();
        $user2fa = new $userTwoFa();
        $user2fa->user_id = auth($this->novaGuard)->user()->id;
        $user2fa->google2fa_secret = $secretKey;
        $user2fa->recovery = $recoveryKeyHashed;
        $user2fa->save();



        $google2fa_url = $this->getQRCodeGoogleUrl(
            config('app.name'),
            auth($this->novaGuard)->user()->email,
            $secretKey,
            500
        );

        $data['google2fa_url'] = $google2fa_url;

        return $data;
    }

    public function verifyOtp()
    {
        $otp = request()->get('otp');
        request()->merge(['one_time_password' => $otp]);

        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if ($authenticator->isAuthenticated()) {
            // otp auth success!

            auth($this->novaGuard)->user()->twoFa()->update([
                'confirmed' => true,
                'google2fa_enabled' => true
            ]);

            return response()->json([
                'message' => __('2FA security successfully activated !')
            ]);
        }

        // auth fail
        return response()->json([
            'message' => __('Invalid OTP !. Please try again')
        ], 422);
    }

    public function toggle2Fa(Request $request)
    {
        $status = $request->get('status') === 1;
        auth($this->novaGuard)->user()->twoFa()->update([
            'google2fa_enabled' => $status
        ]);

        return response()->json([
            'message' => $status ? __('2FA feature enabled!') : __('2FA feature disabled !')
        ]);
    }

    public function getStatus()
    {
        $user = auth($this->novaGuard)->user();

        $res = [
            "registered" => !empty($user->twoFa),
            "enabled" => auth($this->novaGuard)->user()->twoFa->google2fa_enabled ?? false,
            "confirmed" => auth($this->novaGuard)->user()->twoFa->confirmed ?? false
        ];
        return $res;
    }

    public function getQRCodeGoogleUrl($company, $holder, $secret, $size = 200)
    {
        $g2fa = new Google2FA();
        $url = $g2fa->getQRCodeUrl($company, $holder, $secret);

        return self::generateGoogleQRCodeUrl('https://chart.googleapis.com/', 'chart', 'chs=' . $size . 'x' . $size . '&chld=M|0&cht=qr&chl=', $url);
    }

    public static function generateGoogleQRCodeUrl($domain, $page, $queryParameters, $qrCodeUrl)
    {
        $url = $domain .
            rawurlencode($page) .
            '?' . $queryParameters .
            urlencode($qrCodeUrl);

        return $url;
    }

    // Form uses

    public function authenticate(Request $request)
    {


        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if ($authenticator->isAuthenticated()) {
            return redirect()->to(config('nova.path'));
        }

        return back()->withErrors([__('Incorrect OTP !')]);
    }

    public function recover(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('nova-two-factor::recover');
        }

        if (Hash::check($request->get('recovery_code'), auth($this->novaGuard)->user()->twoFa->recovery)) {
            // reset 2fa
            auth($this->novaGuard)->user()->twoFa()->delete();
            return redirect()->to(config('nova.path'));
        } else {
            return back()->withErrors([__('Incorrect recovery code !')]);
        }
    }
}
