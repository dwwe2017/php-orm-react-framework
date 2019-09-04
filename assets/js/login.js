/*
 * Core script to handle all login specific things
 */

const Login = function () {

	"use strict";

	/**
	 * @todo Implement with persistent hash key instead of btoa
	 * @param b
	 * @param k
	 * @param d
	 * @returns {*}
	 * @requires CryptoJS.AES
	 */
	function aes(b, k, d) {
		return CryptoJS.AES.encrypt(b, k, d);
	}

	/**
	 * @todo Could possibly still be needed to form the hash key
	 * @param b
	 * @requires CryptoJS.MD5
	 */
	function md5(b) {
		return CryptoJS.MD5(b)
	}

	/**
	 * @param b
	 * @param k
	 * @param d
	 * @returns {*}
	 * @requires CryptoJS.AES
	 */
	function decrypt(b, k, d) {
		return CryptoJS.AES.decrypt(b, k, d).toString(CryptoJS.enc.Utf8);
	}

	/* * * * * * * * * * * *
	 * Uniform
	 * * * * * * * * * * * */
	const initUniform = function () {
		if ($.fn.uniform) {
			$(':radio.uniform, :checkbox.uniform').uniform();
		}
	};

	/* * * * * * * * * * * *
	 * Sign In / Up Switcher
	 * * * * * * * * * * * */
	const initSignInUpSwitcher = function () {
		// Click on "Don't have an account yet? Sign Up"-text
		$('.sign-up').click(function (e) {
			e.preventDefault(); // Prevent redirect to #

			// Hide login form
			$('.login-form').slideUp(350, function () {
				// Finished, so show register form
				$('.register-form').slideDown(350);
				$('.sign-up').hide();
			});
		});

		// Click on "Back"-button
		$('.back').click(function (e) {
			e.preventDefault(); // Prevent redirect to #

			// Hide register form
			$('.register-form').slideUp(350, function () {
				// Finished, so show login form
				$('.login-form').slideDown(350);
				$('.sign-up').show();
			});
		});
	};

	/* * * * * * * * * * * *
	 * Forgot Password
	 * * * * * * * * * * * */
	const initForgotPassword = function () {
		// Click on "Forgot Password?" link
		$('.forgot-password-link').click(function (e) {
			e.preventDefault(); // Prevent redirect to #

			$('.forgot-password-form').slideToggle(200);
			$('.inner-box .close').fadeToggle(200);
		});

		// Click on close-button
		$('.inner-box .close').click(function () {
			// Emulate click on forgot password link
			// to reduce redundancy
			$('.forgot-password-link').click();
		});
	};

	/* * * * * * * * * * * *
	 * Validation Defaults
	 * * * * * * * * * * * */
	const initValidationDefaults = function () {
		if ($.validator) {
			// Set default options
			$.extend($.validator.defaults, {
				errorClass: "has-error",
				validClass: "has-success",
				highlight: function (element, errorClass, validClass) {
					if (element.type === 'radio') {
						this.findByName(element.name).addClass(errorClass).removeClass(validClass);
					} else {
						$(element).addClass(errorClass).removeClass(validClass);
					}
					$(element).closest(".form-group").addClass(errorClass).removeClass(validClass);
				},
				unhighlight: function (element, errorClass, validClass) {
					if (element.type === 'radio') {
						this.findByName(element.name).removeClass(errorClass).addClass(validClass);
					} else {
						$(element).removeClass(errorClass).addClass(validClass);
					}
					$(element).closest(".form-group").removeClass(errorClass).addClass(validClass);

					// Fix for not removing label in BS3
					$(element).closest('.form-group').find('label[generated="true"]').html('');
				}
			});

			const _base_resetForm = $.validator.prototype.resetForm;
			$.extend($.validator.prototype, {
				resetForm: function () {
					_base_resetForm.call(this);
					this.elements().closest('.form-group')
						.removeClass(this.settings.errorClass + ' ' + this.settings.validClass);
				},
				showLabel: function (element, message) {
					let label = this.errorsFor(element);
					if (label.length) {
						// refresh error/success class
						label.removeClass(this.settings.validClass).addClass(this.settings.errorClass);

						// check if we have a generated label, replace the message then
						if (label.attr("generated")) {
							label.html(message);
						}
					} else {
						// create label
						label = $("<" + this.settings.errorElement + "/>")
							.attr({"for": this.idOrName(element), generated: true})
							.addClass(this.settings.errorClass)
							.addClass('help-block')
							.html(message || "");
						if (this.settings.wrapper) {
							// make sure the element is visible, even in IE
							// actually showing the wrapped element is handled elsewhere
							label = label.hide().show().wrap("<" + this.settings.wrapper + "/>").parent();
						}
						if (!this.labelContainer.append(label).length) {
							if (this.settings.errorPlacement) {
								this.settings.errorPlacement(label, $(element));
							} else {
								label.insertAfter(element);
							}
						}
					}
					if (!message && this.settings.success) {
						label.text("");
						if (typeof this.settings.success === "string") {
							label.addClass(this.settings.success);
						} else {
							this.settings.success(label, element);
						}
					}
					this.toShow = this.toShow.add(label);
				}
			});
		}
	};

	/* * * * * * * * * * * *
	 * Validation for Login
	 * * * * * * * * * * * */
	const initLoginValidation = function () {
		if ($.validator) {
			$('.login-form').validate({
				invalidHandler: function (event, validator) { // display error alert on form submit
					NProgress.start(); // Demo Purpose Only!
					$('.login-form .alert-danger').show();
					NProgress.done(); // Demo Purpose Only!
				},
				submitHandler: function (form) {

					let passphrase = $(form).find("#loginPassphrase").val();

					// Encrypt data for submission
					let username = $(form).find("#loginUsername");
					let usernameVal = username.val();
					username.css("color", "#ffffff");
					username.val(aes(usernameVal, passphrase));

					// Encrypt data for submission
					let password = $(form).find("#loginPassword");
					let passwordVal = password.val();
					password.css("color", "#ffffff");
					password.val(aes(passwordVal, passphrase));

					form.submit();
				}
			});
		}
	};

	/* * * * * * * * * * * *
	 * Validation for Forgot Password
	 * * * * * * * * * * * */
	const initForgotPasswordValidation = function () {
		if ($.validator) {
			$('.forgot-password-form').validate({
				submitHandler: function (form) {

					let passphrase = $(form).find("#resetPassphrase").val();

					// Encrypt data for submission
					let email = $(form).find("#resetEmail");
					let emailVal = email.val();
					email.css("color", "#ffffff");
					email.val(aes(emailVal, passphrase));

					$('.inner-box').slideUp(350, function () {
						$('.forgot-password-form').hide();
						$('.forgot-password-link').hide();
						$('.inner-box .close').hide();

						$('.forgot-password-done').show();

						$('.inner-box').slideDown(350);
					});

					return false;
				}
			});
		}
	};

	/* * * * * * * * * * * *
	 * Validation for Registering
	 * * * * * * * * * * * */
	const initRegisterValidation = function () {
		if ($.validator) {
			$('.register-form').validate({
				invalidHandler: function (event, validator) {
					// Your invalid handler goes here
				},
				submitHandler: function (form) {

					let passphrase = $(form).find("#registerPassphrase").val();

					// Encrypt data for submission
					let username = $(form).find("#registerUsername");
					let usernameVal = username.val();
					username.css("color", "#ffffff");
					username.val(aes(usernameVal, passphrase));

					// Encrypt data for submission
					let password = $(form).find("#registerPassword");
					let passwordVal = password.val();
					password.css("color", "#ffffff");
					password.val(aes(passwordVal, passphrase));

					// Encrypt data for submission
					let passwordConfirm = $(form).find("#registerPasswordConfirm");
					let passwordConfirmVal = passwordConfirm.val();
					passwordConfirm.css("color", "#ffffff");
					passwordConfirm.val(aes(passwordConfirmVal, passphrase));

					// Encrypt data for submission
					let email = $(form).find("#registerEmail");
					let emailVal = email.val();
					email.css("color", "#ffffff");
					email.val(aes(emailVal, passphrase));

					form.submit();
				}
			});
		}
	};

	return {

		// main function to initiate all plugins
		init: function () {
			initUniform(); // Styled checkboxes
			initSignInUpSwitcher(); // Handle sign in and sign up specific things
			initForgotPassword(); // Handle forgot password specific things

			// Validations
			initValidationDefaults(); // Extending jQuery Validation defaults
			initLoginValidation(); // Validation for Login (Sign In)
			initForgotPasswordValidation(); // Validation for the Password-Forgotten-Widget
			initRegisterValidation(); // Validation for Registering (Sign Up)
		},

	};

}();