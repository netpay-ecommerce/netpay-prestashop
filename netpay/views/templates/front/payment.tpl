{*
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    NetPay SPA
*  @copyright 2019 NetPay
*  @license   LICENSE.txt
*
*}
<head>
    <style>
        .submitButton{
            position: relative;
            display: inline-block;
            padding: 5px 7px;
            border: 1px solid #cc9900;
            font-weight: bold;
            color: black;
            background: url("../modules/netpay/views/img/bg_bt.gif") repeat-x 0 0 #f4b61b;
            cursor: pointer;
            white-space: normal;
            text-align: left;
        }
        .submitButton:hover{
            color: white;
        }
    </style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="../modules/netpay/views/js/payments.js"></script>
<script src="../modules/netpay/views/js/cybs_devicefingerprint.js"></script>
<script>
    var deviceFingerprintID = "{$deviceFingerprintID}";
    var orgId = "{$orgId}";
    var mid = "{$mid}";
    cybs_dfprofiler(orgId, deviceFingerprintID, mid); 
</script>
</head>
<div id="error-msg" class="netpay-error-message alert alert-danger" style="display: none;"></div>
<div id="warning-msg" class="netpay-error-message alert alert-warning" style="display: none;"></div>
<table border="0">
    <tr>
        <td colspan="2">
            <img src="../modules/netpay/views/img/netpay.png" alt="{l s='NetPay' mod='netpay'}"/>
        </td>
    </tr>
    <tr>
        <td>
            <form class="pg_frm" name="pg_frm" id="pg_frm">
                <div class="form-group card_type" id="card-type">
                    <label>Tipo:</label>
                    <select name='cardType' id='cardType'>
                        <option value="001">Visa</option>
                        <option value="002">MasterCard </option>
                        <option value="003">American Express</option>
                    </select>
                </div>
                <div class="form-group" id="card-number-field">
                    <label for="cardNumber">Número de la tarjeta:</label>
                    <input type="hidden" name="payment-method" value="card">
                    <input type="hidden" name="deviceFingerprintID" value="{$deviceFingerprintID}">
                    <input type="text" class="form-control" id="cardNumber" name='cardNumber' maxlength="16">
                </div>
                <div class="form-group" id="expiration-date">
                    <label>Fecha de expiración:</label>
                    <select name='expirationMonth' id='expirationMonth'>
                        <option value="01">01</option>
                        <option value="02">02 </option>
                        <option value="03">03</option>
                        <option value="04">04</option>
                        <option value="05">05</option>
                        <option value="06">06</option>
                        <option value="07">07</option>
                        <option value="08">08</option>
                        <option value="09">09</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                    </select>
                    <select name='expirationYear' id='expirationYear'>
                        {foreach from=$expiration_years item=expiration_year}
                            <option value="{$expiration_year.year}"> {$expiration_year.description}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group CVV">
                    <label for="cvv">CVV:</label>
                    <input type="password" class="form-control" id="cvv" name='cvv' maxlength="4">
                </div>
                <div class="form-group owner">
                    <label for="owner">Nombre del titular:</label>
                    <input type="text" class="form-control" id="owner" name='cardHolderName' maxlength="70">
                </div>
                <div class="form-group msi" id="dev_msi">
                    <label>Promoción:</label>
                    <select name='msi' id='msi'>
                        <option value="000000">Pago único</option>
                        {if $msi_000303 != null}
                        <option value="000303">3 Meses sin intereses </option>
                        {/if}
                        {if $msi_000603 != null}
                        <option value="000603">6 Meses sin intereses </option>
                        {/if}
                        {if $msi_000903 != null}
                        <option value="000903">9 Meses sin intereses </option>
                        {/if}
                        {if $msi_001203 != null}
                        <option value="001203">12 Meses sin intereses </option>
                        {/if}
                        {if $msi_001803 != null}
                        <option value="001803">18 Meses sin intereses </option>
                        {/if}
                    </select>
                </div>
                
                <table border="0" style="width:100%">
                    <tr>
                        <th>{l s='Total' mod='netpay'}</th>
                        <th>{l s='Método de pago' mod='netpay'}</th>
                    </tr>
                    <tr>
                        <td>
                            <span id="amount">{$total} {$currency}</span>
                        </td>
                        <td>NetPay</td>
                    </tr>
                    <!--<tr>
                        <td colspan="2">
                            <input type="submit" name="pg_button" id="pg_button" value="{l s='Realizar pago' mod='netpay'}" class="submitButton" />
                        </td>
                    </tr>-->
                </table>
            </form>
        </td>
    </tr>
</table>