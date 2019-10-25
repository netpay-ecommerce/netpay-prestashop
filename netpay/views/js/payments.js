$(function () {
    let $form = '';

    var cardType = $( "select#cardType option:checked" ).val();
    setLimit(cardType);

    $('#cardType').on('change', function () {
        var cardType = $( "select#cardType option:checked" ).val();
        $( "#cardNumber").val("");
        $( "#cvv").val("");
        setLimit(cardType);
    });

    const initNetPay = async () => {
        'use strict';

        // Create references to the submit button.
        const $submit = $('#payment-confirmation button[type="submit"], .submitButton');
        const $submitButtons = $('#payment-confirmation button[type="submit"], .submitButton');
        const submitInitialText = $submitButtons.text();

        $form = $('#pg_frm');
        let payment = '';
        let disableText = '';

        // Global variable to store the PaymentIntent object.
        let paymentIntent;

        // Disabled card form (enter button)
        $form.on('submit', (event) => {
            event.preventDefault();
        });

        /**
        * Handle the form submission.
        */
        $submit.click(async event => {
            if (event.result == undefined) {
                if ($('#div_cash:visible').length) {
                    disableText = event.currentTarget;
                    disableSubmit(disableText, 'Procesando…');
                    event.preventDefault();

                    $.ajax({
                        type: 'POST',
                        url: '../modules/netpay/callback.php?method=cash',
                        dataType: 'html',
                        data: '',
                        success: function (response) {
                            handlePayment(response);
                        },
                        error: function (response) {
                            handlePayment(response);
                        }
                    });

                    event.stopPropagation();

                    return false;
                }
                event.preventDefault();
                $form = $('#pg_frm');
                var isValid;
                $("input", $form).each(function () {
                    var element = $(this);
                    if (element.val() == "") {
                        isValid = false;
                    }
                });
                
                var cardNumber = $( "#cardNumber").val();
                var cvv = $( "#cvv").val();
                var cardLength = cardLengthValue();
                var cvvLength = cvvLengthValue();
                var isValidDate = validateExpiryDate();

                
                if (isValid === false) {
                    errorMessage("Todos los campos son requeridos.");
                }
                else if (!isValidDate) {
                    errorMessage("La tarjeta ha expirado.");
                }
                else if(cardNumber.length < cardLength) {
                    errorMessage("La tarjeta debe de ser de " + cardLength + " dígitos.");
                }
                else if(cvv.length < cvvLength) {
                    errorMessage("El CVV debe de ser de " + cvvLength + " dígitos.");
                }
                else {
                    payment = $('input[name="payment-method"]', $form).val();
                    disableText = event.currentTarget;

                    // Disable the Pay button to prevent multiple click events.
                    disableSubmit(disableText, 'Procesando…');

                    if (payment === 'card') {
                        $.ajax({
                            type: 'POST',
                            url: '../modules/netpay/callback.php?method=card',
                            dataType: 'html',
                            data: $('#pg_frm').serialize(),
                            success: function (response) {
                                handlePayment(response);
                            },
                            error: function (response) {
                                handlePayment(response);
                            }
                        });
                    }

                    event.stopPropagation();
                }

                return false;
            }
        });

        function handlePaymentCash(value) {
            var response = JSON.parse(value);
            if (response.error) {
                updateError(response.error);
                enableSubmit($submitButtons);
            } else {
                successMessage('La referencia a pagar es: ' + response.reference);
                disableSubmit(disableText, 'Finalizado');
            }
        }

        // Handle new PaymentIntent result
        function handlePayment(value) {
            if (value == '') {
                setError();
            }
            else if (value.status == undefined) {
                var response = JSON.parse(value);
                if (response.error) {
                    updateError(response.error);
                    enableSubmit($submitButtons);
                } else {
                    window.location.replace(response.url);
                }
            }
            else {
                setError();
            }
        }

        function setError() {
            updateError('Error al procesar el pago.');
            enableSubmit($submitButtons);
        }

        // Update error message
        function updateError(error) {
            $('#warning-msg').hide();
            $('#error-msg').text(error);
            $('#error-msg').show();
        }

        function successMessage(error) {
            $('#warning-msg').hide();
            $('#success-msg').text(error);
            $('#success-msg').show();
        }

        function disableSubmit(element, text) {
            $(element).prop('disabled', true);
            $(element).text(text ? text : submitInitialText);
        }

        function enableSubmit(element) {
            $(element).prop('disabled', false);
            $(element).text(submitInitialText);
        }

        function cvvLengthValue() {
            var cardType = $( "select#cardType option:checked" ).val();
            switch(cardType) {
                case '001':
                case '002':
                    return 3;
                case '003':
                    return 4;
              }
        }
    
        function cardLengthValue() {
            var cardType = $( "select#cardType option:checked" ).val();
            switch(cardType) {
                case '001':
                case '002':
                    return 16;
                case '003':
                    return 15;
              }
        }

        function errorMessage(message) {
            $('#error-msg').hide();
            $('#warning-msg').text(message);
            $('#warning-msg').show();
    
            $form.on('submit', (event) => {
                event.preventDefault();
            });
            event.stopPropagation();
            enableSubmit($submitButtons);
        }

        function validateExpiryDate() {
            var exMonth = $("#expirationMonth" ).val();
            var exYear = "20" + $("#expirationYear" ).val();
            let today = new Date();
            let someday = new Date();
            someday.setFullYear(exYear, exMonth, 1);

            if (someday < today) {
                return false;
            }
            return true;
        }
    };

    initNetPay();

    const observer = new MutationObserver((mutations) => {
        $.each(mutations, function (i, mutation) {
            const addedNodes = $(mutation.addedNodes);
            const selector = '#pg_frm';
            const filteredEls = addedNodes.find(selector).addBack(selector);
            if (filteredEls.length) {
                initNetPay();
            }
        })
    });

    observer.observe(document.body, { childList: true, subtree: true });

    function setLimit(cardType) {
        switch(cardType) {
            case '001':
            case '002':
                $("#cvv").attr('maxlength','3');
                $("#cvv").attr('minlength','3');
                $("#cardNumber").attr('maxlength','16');
                $("#cardNumber").attr('minlength','16');
                break;
            case '003':
                $("#cvv").attr('maxlength','4');
                $("#cvv").attr('minlength','4');
                $("#cardNumber").attr('maxlength','15');
                $("#cardNumber").attr('minlength','15');
              break;
          }
    }
})
