angular.module('continuousPipeRiver')
    .service('$intercom', function(INTERCOM_ENABLED, INTERCOM_APPLICATION_ID) {
        this.isEnabled = function() {
            return INTERCOM_ENABLED === 'true';
        };

        this.configure = function(user) {
            if (!this.isEnabled()) {
                return;
            }

            window.Intercom("boot", {
                app_id: INTERCOM_APPLICATION_ID,
                user_id: user.username,
                email: user.email,
                custom_launcher_selector: '#contact-us-launcher',
                hide_default_launcher: true
            });
        };

        this.trackEvent = function(name, event) {
            if (!this.isEnabled()) {
                return;
            }

            window.Intercom('trackEvent', name, event);
        }
    });
