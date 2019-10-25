{config_load file="test.conf" section="setup"}
{include file="header.tpl" title="NetPay - Pagos con tarjetas"}

<div class="container-fluid">
        <div class="creditCardForm">
            <div class="payment">
                <form action="./callback.php" method="post">
                    <div class="form-group" id="card-number-field">
                        <label for="cardNumber">Número de la tarjeta</label>
                        <input type="text" class="form-control" id="cardNumber" name='cardNumber'>
                    </div>
                    <div class="form-group" id="expiration-date">
                        <label>Fecha de expiración</label>
                        <select name='expirationMonth'>
                            <option value="01">Enero</option>
                            <option value="02">Febrero </option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        <select name='expirationYear'>
                            {foreach from=$expiration_years item=expiration_year}
                                <option value="{$expiration_year.year}"> {$expiration_year.description}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group CVV">
                        <label for="cvv">CVV</label>
                        <input type="text" class="form-control" id="cvv" name='cvv'>
                    </div>
                    <div class="form-group owner">
                        <label for="owner">Nombre del titular</label>
                        <input type="text" class="form-control" id="owner" name='cardHolderName'>
                    </div>
                    <div class="form-group card_type" id="card-type">
                        <label>Tipo</label>
                        <select name='cardType'>
                            <option value="001">Visa</option>
                            <option value="002">MasterCard </option>
                            <option value="003">American Express</option>
                        </select>

                        <img src="images/visa.jpg" id="visa" height="40" width="40">
                        <img src="images/mastercard.jpg" id="mastercard" height="40" width="40">
                        <img src="images/amex.jpg" id="amex" height="40" width="40">
                    </div>
                    <div class="form-group" id="pay-now">
                        <button type="submit" class="btn btn-default">Pagar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

{include file="footer.tpl"}
