<template>
  <LoadingView :loading="loading">
    <heading class="mb-6">{{ __('Two factor auth (Google 2FA)') }}</heading>

    <LoadingCard :loading="loading" class="o1-p-4">
      <div class="o1-grid o1-grid-cols-2 o1-gap-4">
        <div class="">
          <div class="" v-if="status.confirmed == 1">
            <p class="mb-4 text-slate-900 dark:text-slate-400">
              {{ __('Update your two factor security settings') }}
            </p>

            <div class="o1-flex o1-items-center o1-mb-4">
              <input
                v-model="status.enabled"
                :value="1"
                id="op-enable"
                type="radio"
                class="o1-w-4 o1-h-4 o1-border-gray-300 o1-focus:ring-2 o1-focus:ring-blue-300"
              />
              <label for="op-enable" class="o1-block o1-ml-2 o1-text-sm o1-font-medium dark:text-white">
                {{ __('Enable') }}
              </label>
            </div>

            <div class="o1-flex o1-items-center o1-mb-4">
              <input
                v-model="status.enabled"
                :value="0"
                id="op-disable"
                type="radio"
                class="o1-w-4 o1-h-4 o1-border-gray-300 o1-focus:ring-2 o1-focus:ring-blue-300"
              />
              <label for="op-disable" class="o1-block o1-ml-2 o1-text-sm o1-font-medium dark:text-white">
                {{ __('Disable') }}
              </label>
            </div>

            <br />

            <LoadingButton @click="toggle">{{ __('Update Settings') }}</LoadingButton>
          </div>

          <div v-else class="">
            <p>
              {{
                __(
                  'Two factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. Two factor authentication protects against phishing, social engineering and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.'
                )
              }}
            </p>

            <h3 class="o1-my-2 o1-text-xl">{{ __('Recovery codes') }}</h3>

            <p class="o1-mb-3">
              {{
                __(
                  'Recovery code are used to access your account in the event you cannot receive two-factor authentication codes.'
                )
              }}
            </p>
            <span
              class="o1-bg-gray-100 o1-text-gray-800 o1-text-xs o1-font-semibold o1-mr-2 o1-px-2.5 o1-py-0.5 o1-rounded"
              >{{ __('Step 01') }}</span
            >
            <p class="no-print o1-my-4 o1-text-md">
              {{ __('Download, print or copy your recovery code before continuing two-factor authentication setup.') }}
            </p>

            <div
              class="o1-mb-4 o1-border-dashed o1-border-2 o1-border-light-blue dark:border-gray-500 o1-p-4 o1-rounded-lg o1-text-center o1-bg-gray-50 dark:bg-gray-700"
            >
              <h2 class="o1-text-xl o1-text-black">{{ twofa.recovery }}</h2>
              <a
                class="o1-text-blue-700"
                @click.prevent="downloadAsText('recover_code.txt', twofa.recovery)"
                href="#"
                >{{ __('Download') }}</a
              >
            </div>

            <span
              class="o1-bg-gray-100 o1-text-gray-800 o1-text-xs o1-font-semibold o1-mr-2 o1-px-2.5 o1-py-0.5 o1-rounded"
              >{{ __('Step 02') }}</span
            >

            <div class="o1-my-4 o1-text-md">
              {{ __('Scan this QR code using Google authenticator to setup & enter OTP to activate 2FA') }}

              <input
                v-model="form.otp"
                @keyup="autoSubmit()"
                placeholder="Enter OTP here"
                type="text"
                class="form-control form-input form-input-bordered o1-my-4"
              />
              <br />
              <LoadingButton
                :loading="loading"
                :disabled="loading"
                @click="confirmOtp"
                class="btn btn-default btn-primary"
                >{{ __('Activate 2FA') }}</LoadingButton
              >
            </div>
          </div>
        </div>
        <div class="o1-h-full">
          <div v-if="!status.confirmed" class="o1-flex o1-justify-center o1-content-center o1-w-full o1-p-8">
            <img width="250" :src="twofa.google2fa_url" alt="qr_code" />
          </div>
        </div>
      </div>
    </LoadingCard>
  </LoadingView>
</template>

<script>
export default {
  data() {
    return {
      twofa: [],
      form: {},
      status: null,
      loading: true,
    };
  },

  mounted() {
    this.getStatus();
    this.getRecoveryCodes();
  },

  methods: {
    getStatus() {
      Nova.request()
        .get('/nova-vendor/nova-two-factor/status')
        .then(res => {
          this.status = res.data;
          this.loading = false;
        });
    },

    getRecoveryCodes() {
      Nova.request()
        .get('/nova-vendor/nova-two-factor/register')
        .then(res => {
          this.twofa = res.data;
        });
    },

    toggle() {
      Nova.request()
        .post('/nova-vendor/nova-two-factor/toggle', {
          status: this.status.enabled,
        })
        .then(res => {
          Nova.success(res.data.message);
        });
    },

    confirmOtp() {
      Nova.request()
        .post('/nova-vendor/nova-two-factor/confirm', this.form)
        .then(res => {
          Nova.success(res.data.message);
          this.getStatus();
        })
        .catch(err => {
          Nova.error(err.response.data.message);
        });
    },

    autoSubmit() {
      if (this.form.otp.length === 6) {
        this.confirmOtp();
      }
    },

    downloadAsText(filename, text) {
      var element = document.createElement('a');
      element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', filename);

      element.style.display = 'none';
      document.body.appendChild(element);

      element.click();

      document.body.removeChild(element);
    },
  },

  computed: {
    //
  },
};
</script>
