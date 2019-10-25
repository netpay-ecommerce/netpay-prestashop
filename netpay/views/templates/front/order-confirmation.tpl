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
*  @author    NetPay
*  @copyright 2019 NetPay
*  @license   LICENSE.txt
*
*}

<style>
    .responsive { 
        width: 70%;
        height: auto;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
</style>

{literal}
<script type="text/javascript">
function printReceipt() {
  window.print();
}
</script>

{/literal}

{if $isReference eq 1}
    <button onclick="printReceipt();" style="float: right;">Imprimir</button>
    <br><br>
    <div style="background-color:light" align="center">
        <h2>Referencia: {$reference}</h2>
    </div>
    Los siguientes comercios te cobrarán una comisión al momento de realizar el pago:
    <div id="div_cash">
        <img src="{$paymentsOption}" class="responsive" alt="{l s='NetPay' mod='netpay'}"/>
    </div>

    <p>
    <h2>INSTRUCCIONES:</h2>
        1.- Acude a una de las tiendas listadas más cercana.<br>
        2.- Indica en caja que quieres realizar un pago de referencia de Banorte al servicio de Net Pay.<br>
        3.- Dicta al cajero el número de la referencia que está en esta pantalla para que la teclee directamente en la pantalla de venta.<br>
        4.- Realiza el pago correspondiente con dinero en efectivo.<br>
        5.- Al confirmar el pago, el cajero te entregará un comprobante impreso. <b>En él podrás verificar que se haya realizado correctamente</b>.Conserva este comprobante de pago.<br>
    </p>

    <p>
    * El pago se verá reflejado en las próximas 24 a 48 horas hábiles.
    </p>

    <p>
    Al completar estos pasos, {$site} te enviará un correo confirmando tu pago de manera inmediata.
    </p>
{else}
    Muchas gracias por tu compra.
{/if}