# JetFormBuilder Stripe Gateway
Addon for JetFormBuilder & JetEngine Forms

# ChangeLog

## 2.0.3
* FIX: Getaways not working when Jet Appointments Booking is enabled

## 2.0.2
* ADD: Custom form events for Stripe subscription actions

## 2.0.1
* FIX: Form actions execution after Stripe subscription
  
## 2.0.0
* ADD: Subscription support

## 1.1.2
* FIX: Successful access token update with empty keys
* Tweak: Update jfb-addon-core to `1.1.11`

## 1.1.1
* ADD: `jet-form-builder/gateways/before-create` php hook
* FIX: Request body was changed according to [stripe upgrade](https://stripe.com/docs/upgrades#2022-08-01)

## 1.1.0
* ADD: Compatibility with Form Records
* UPD: Redirect to the checkout on the server-side

## 1.0.4
* FIX: JetAppointment compatibility

## 1.0.3
* ADD: filter `jet-form-builder/stripe/payment-methods`, for change payment method types
* Tweak: Removed unnecessary hook

## 1.0.2
* Tweak: add license manager

## 1.0.1
* FIX: Error when global settings did not use

## 1.0.0
* Initial release
