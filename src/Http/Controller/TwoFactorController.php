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
        $user = auth($this->novaGuard)->user();

        if ($user->twoFa?->confirmed) {
            return response()->json(['message' => __('Already verified!')], 400);
        }

        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();

        $recoveryKey = strtoupper(Str::random(16));
        $recoveryKey = str_split($recoveryKey, 4);
        $recoveryKey = implode('-', $recoveryKey);
        $recoveryKeyHashed = bcrypt($recoveryKey);

        $data['recovery'] = $recoveryKey;
        TwoFa::where('user_id', $user->id)->delete();

        $user2fa = new TwoFa();
        $user2fa->user_id = $user->id;
        $user2fa->google2fa_secret = $secretKey;
        $user2fa->recovery = $recoveryKeyHashed;
        $user2fa->save();

        $googleTwoFaUrl = $this->getQRCodeGoogleUrl(config('app.name'), $user->email, $secretKey, 500);
        $data['google2fa_url'] = $googleTwoFaUrl;

        return $data;
    }

    public function verifyOtp()
    {
        $otp = request()->get('otp');
        request()->merge(['one_time_password' => $otp]);

        $authenticator = app(TwoFaAuthenticator::class)->boot(request());

        if ($authenticator->isAuthenticated()) {
            auth($this->novaGuard)->user()->twoFa()->update(['confirmed' => true, 'google2fa_enabled' => true]);

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
        auth($this->novaGuard)->user()->twoFa()->update(['google2fa_enabled' => $status]);

        return response()->json([
            'message' => __($status ? '2FA feature enabled!' : '2FA feature disabled!')
        ]);
    }

    public function getStatus()
    {
        $user = auth($this->novaGuard)->user();

        return [
            'registered' => !empty($user->twoFa),
            'enabled' => $user->twoFa->google2fa_enabled ?? false,
            'confirmed' => $user->twoFa->confirmed ?? false
        ];
    }

    public function getQRCodeGoogleUrl($company, $holder, $secret, $size = 200)
    {
        $g2fa = new Google2FA();
        $url = $g2fa->getQRCodeUrl($company, $holder, $secret);
        return self::generateGoogleQRCodeUrl('https://chart.googleapis.com/', 'chart', 'chs=' . $size . 'x' . $size . '&chld=M|0&cht=qr&chl=', $url);
    }

    public static function generateGoogleQRCodeUrl($domain, $page, $queryParameters, $qrCodeUrl)
    {
        return $domain . rawurlencode($page) . '?' . $queryParameters . urlencode($qrCodeUrl);
    }

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

        $user = auth($this->novaGuard)->user();
        if (Hash::check($request->get('recovery_code'), $user->twoFa->recovery)) {
            $user->twoFa()->delete();
            return redirect()->to(config('nova.path'));
        }

        return back()->withErrors([__('Incorrect recovery code !')]);
    }
}
