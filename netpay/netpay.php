<?php
/**
* netpay.mx
*
* @author    NetPay
* @copyright 2019 NetPay
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

require_once (dirname(__FILE__)  . '/lib/netpay-php/init.php');

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use \NetPay\Config;

/*This checks for the Prestashop existence */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/NetPayCustomClass.php');

class NetPay extends PaymentModule
{   
    private $html       = '';
    private $postErrors = array();

    public $username;
    public $password;
    public $storeIdAcq;
    public $storeApiKey;
    public $orgId;
    public $mid;
    public $transtype;
    public $msi_000303;
    public $msi_000603;
    public $msi_000903;
    public $msi_001203;
    public $msi_001803;

    public $sandbox;
    public $mode;
    public $gateway     = 'https://developers.netpay.com.mx';

    /**
     * Metodo constructor en donde se declaran las variables con valores por default
     */
    public function __construct()
    {
        $this->name                     = 'netpay';
        $this->tab                      = 'payments_gateways';
        $this->version                  = '1.0.1';
        $this->author                   = 'Netpay';
        $this->need_instance            = 1;
        $this->currencies               = true;
        $this->currencies_mode          = 'checkbox';
        //$this->module_key               = '';
        $this->ps_versions_compliancy   = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap                = true;

        $config                         = Configuration::getMultiple(array(
            'USERNAME',
            'PASSWORD', 
            'STOREIDACQ',
            'STOREAPIKEY',
            'ORGID',
            'MSI_000303',
            'MSI_000603',
            'MSI_000903',
            'MSI_001203',
            'MSI_001803',
            'MID',
            'TRANSTYPE',

            'SANDBOX_USERNAME',
            'SANDBOX_PASSWORD', 
            'SANDBOX_STOREIDACQ',
            'SANDBOX_STOREAPIKEY',
            'SANDBOX_ORGID',
            'SANDBOX_MSI_000303',
            'SANDBOX_MSI_000603',
            'SANDBOX_MSI_000903',
            'SANDBOX_MSI_001203',
            'SANDBOX_MSI_001803',
            'SANDBOX_MID',
            'SANDBOX_TRANSTYPE',

            'NETPAY_IPN',
            'sandbox'
        ));

        self::setConfigValues($config);

        parent::__construct();
        
        $this->displayName  = $this->l('NetPay');
        $this->description  = $this->l('NetPay es un proveedor de servicios de pago en línea que ofrece una amplia variedad ');
        $this->description  .= $this->l('de métodos de pago con tarjetas Visa, MasterCard y American Express. ');
        $this->description  .= $this->l('Información adicional:');
        
        $this->description .= '<br><b><a href="https://www.netpay.mx" target="_blank">'.
            ' https://www.netpay.mx</a><br>
            <a href="https://developers.netpay.com.mx" target="_blank"> https://developers.netpay.com.mx</a></b>';
        $this->confirmUninstall = $this->l('Estas seguro que deseas desinstalar?');

        if (!Configuration::get('NETPAY')) {
            $this->warning = $this->l('No se ha encontrado el nombre del payment gateway');
        }
    }

    /**
     * Se asignan los valores de las variables config para el funcionamiento del payment gateway
     */
    private function setConfigValues($config) {
        // Properties username and password get their values from the database.

        if (isset($config['sandbox'])) {
            $this->sandbox = $config['sandbox'];
        }

        $mode = $this->sandbox ? 'SANDBOX_' : '';
        $this->mode = $mode;

        if (isset($config[$mode . 'USERNAME'])) {
            $this->username = $config[$mode  . 'USERNAME'];
        }
        if (isset($config[$mode . 'PASSWORD'])) {
            $this->password = $config[$mode . 'PASSWORD'];
        }
        if (isset($config[$mode  . 'STOREIDACQ'])) {
            $this->storeIdAcq = $config[$mode . 'STOREIDACQ'];
        }
        if (isset($config[$mode . 'STOREAPIKEY'])) {
            $this->storeApiKey = $config[$mode . 'STOREAPIKEY'];
        }
        if (isset($config[$mode . 'ORGID'])) {
            $this->orgId = $config[$mode . 'ORGID'];
        }
        if (isset($config[$mode . 'MSI_000303'])) {
            $this->msi_000303 = $config[$mode . 'MSI_000303'];
        }
        if (isset($config[$mode . 'MSI_000603'])) {
            $this->msi_000603 = $config[$mode . 'MSI_000603'];
        }
        if (isset($config[$mode . 'MSI_000903'])) {
            $this->msi_000903 = $config[$mode . 'MSI_000903'];
        }
        if (isset($config[$mode . 'MSI_001203'])) {
            $this->msi_001203 = $config[$mode . 'MSI_001203'];
        }
        if (isset($config[$mode . 'MSI_001803'])) {
            $this->msi_001803 = $config[$mode . 'MSI_001803'];
        }
        if (isset($config[$mode . 'MID'])) {
            $this->mid = $config[$mode . 'MID'];
        }
        if (isset($config[$mode . 'TRANSTYPE'])) {
            $this->transtype = $config[$mode . 'TRANSTYPE'];
        }
    }

/**********************************************************************INSTALL**********************************************************************/
    /**
     * Metodo en donde se instalan  los estatus de las transacciones y los hooks a ejecutar
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        //EN, ES, FR, PT
        
        if (!Configuration::get('NETPAY_ORDER_PENDING')) {
            Configuration::updateValue('NETPAY_ORDER_PENDING', $this->addState(
                'Order Pending',
                'Orden Pendiente',
                'Ordre en Attente',
                'Pedido Pendente',
                '#FF8C00'
            ));
        }
        if (!Configuration::get('NETPAY_ORDER_PREAUTH')) {
            Configuration::updateValue('NETPAY_ORDER_PREAUTH', $this->addState(
                'Order PreAuth',
                'Orden PreAuth',
                'Ordre en PreAuth',
                'Pedido PreAuth',
                '#fff200'
            ));
        }
        if (!Configuration::get('NETPAY_ORDER_PROCESSING')) {
            Configuration::updateValue('NETPAY_ORDER_PROCESSING', $this->addState(
                'Processing Payment',
                'Procesando Pago',
                'Traitement du Paiement',
                'Processando o Pagamento',
                '#DDEEFF'
            ));
        }
        if (!Configuration::get('NETPAY_ORDER_SUCCESS')) {
            Configuration::updateValue('NETPAY_ORDER_SUCCESS', $this->addState(
                'Successful',
                'Completada',
                'Terminé',
                'Concluído',
                '#32D600',
                true
            ));
        }
        if (!Configuration::get('NETPAY_ORDER_CANCELLED')) {
            Configuration::updateValue('NETPAY_ORDER_CANCELLED', $this->addState(
                'Order Cancelled',
                'Orden Cancelada',
                'Annulé',
                'Cancelada',
                '#8000ff'
            ));
        }

        if (!parent::install() || !Configuration::updateValue('NetPay', 'Pagar con NetPay') ||
            !$this->registerHook('paymentOptions') || !$this->registerHook('OrderConfirmation') ||
            !$this->registerHook('actionOrderStatusPostUpdate')) {
            return false;
        }

        if(!$this->install_database()) {
            return false;
        }

        return true;
    }

    private function install_database() {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'netpay_order` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` int(11) unsigned NOT NULL,
            `id_cart` int(11) unsigned NOT NULL,
            `transaction_token_id` varchar(100) NOT NULL,
            `preauth` smallint not null default 0,
            `is_reference` smallint not null default 0,
            `reference` varchar(100) NOT NULL,
            `register_date` datetime NOT NULL default "0000-00-00 00:00:00",
            `date_paid` datetime NOT NULL default "0000-00-00 00:00:00",
            `is_cancelled` smallint not null default 0,
            `date_cancelled` datetime NOT NULL default "0000-00-00 00:00:00",
            PRIMARY KEY (`id`)
        ) ENGINE= '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'netpay_conf` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `pay_in_cash` smallint not null default 0,
            PRIMARY KEY (`id`)
        ) ENGINE= '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')) {
            return false;
        }
        return true;
    }
    
    /**
     * Método para asignar el texto de la descripción del estatus de la transacción en el idioma configurado y el ícono que llevará
     */
    private function addState($en, $es, $fr, $pt, $color, $paid = false)
    {
        $orderState = new OrderState();
        $orderState->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::substr($language['language_code'], 0, 2) == 'es') {
                $orderState->name[$language['id_lang']] = 'NetPay - '.$es;
            } elseif (Tools::substr($language['language_code'], 0, 2) == 'fr') {
                $orderState->name[$language['id_lang']] = 'NetPay - '.$fr;
            } elseif (Tools::substr($language['language_code'], 0, 2) == 'pt') {
                $orderState->name[$language['id_lang']] = 'NetPay - '.$pt;
            } else {
                $orderState->name[$language['id_lang']] = 'NetPay - '.$en;
            }
        }

        $orderState->send_email     = true;
        $orderState->module_name    = 'netpay';
        $orderState->color          = $color;
        $orderState->hidden         = false;
        $orderState->delivery       = false;
        $orderState->logable        = false;
        $orderState->paid           = $paid;
        
        if ($orderState->add()) {
            $data1 = dirname(__FILE__).'/views/img/os_netpay.gif';
            $data2 = dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif';
            copy($data1, $data2);
            return $orderState->id;
        }
    }

    /**
     * Desinstala los estatus de las transacciones y las variables de configuración instaladas
     */
    public function uninstall() {
        $config = Configuration::getMultiple(
            array(
                'NETPAY_ORDER_PENDING', 
                'NETPAY_ORDER_PREAUTH', 
                'NETPAY_ORDER_PROCESSING', 
                'NETPAY_ORDER_SUCCESS',
                'NETPAY_ORDER_CANCELLED'
            )
        );
        
        $NetPayCS = new NetpayCustomClass();
        
        if (!Configuration::deleteByName($this->mode . 'USERNAME')
            || !Configuration::deleteByName($this->mode . 'PASSWORD')
            || !Configuration::deleteByName($this->mode . 'STOREIDACQ')
            || !Configuration::deleteByName($this->mode . 'STOREAPIKEY')
            || !Configuration::deleteByName($this->mode . 'ORGID')
            || !Configuration::deleteByName($this->mode . 'MID')
            || !Configuration::deleteByName($this->mode . 'MSI_000303')
            || !Configuration::deleteByName($this->mode . 'MSI_000603')
            || !Configuration::deleteByName($this->mode . 'MSI_000903')
            || !Configuration::deleteByName($this->mode . 'MSI_001203')
            || !Configuration::deleteByName($this->mode. 'MSI_001803')
            || !Configuration::deleteByName($this->mode. 'TRANSTYPE')
            || !$NetPayCS->deleteOrderState($config['NETPAY_ORDER_PENDING'])
            || !$NetPayCS->deleteOrderState($config['NETPAY_ORDER_PREAUTH'])
            || !$NetPayCS->deleteOrderState($config['NETPAY_ORDER_SUCCESS'])
            || !$NetPayCS->deleteOrderState($config['NETPAY_ORDER_PROCESSING'])
            || !$NetPayCS->deleteOrderState($config['NETPAY_ORDER_CANCELLED'])
            || !Configuration::deleteByName('NETPAY_ORDER_PENDING')
            || !Configuration::deleteByName('NETPAY_ORDER_PREAUTH')
            || !Configuration::deleteByName('NETPAY_ORDER_PROCESSING')
            || !Configuration::deleteByName('NETPAY_ORDER_SUCCESS')
            || !Configuration::deleteByName('NETPAY_ORDER_CANCELLED')
            || !parent::uninstall()) {
            error_log('Uninstall error: check the nine lines before line '. __LINE__. ' in file ' . __FILE__);
            return false;
        }
        return true;
    }
/***************************************************************************************************************************************************/
/*****************************************************ADMIN PAGE*******************************************************/
    private function postValidation() {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue($this->mode . 'USERNAME')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' username"');
            }
            if (!Tools::getValue($this->mode . 'STOREIDACQ')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' storeIdAcq"');
            }
            if (!Tools::getValue($this->mode . 'STOREAPIKEY')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' storeApiKey"');
            }
            if (!Tools::getValue($this->mode .  'ORGID')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' orgId"');
            }
            if (!Tools::getValue($this->mode . 'MID')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' MID"');
            }
            if (!Tools::getValue($this->mode . 'TRANSTYPE')) {
                $this->postErrors[] = $this->l('Por favor escribe el valor del siguiente campo requerido: "'.$this->mode.' TRANSTYPE"');
            }
        }
    }
    
    private function postProcess() {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('SANDBOX_USERNAME', Tools::getValue('SANDBOX_USERNAME'));
            if(!empty(Tools::getValue('SANDBOX_PASSWORD'))) {
                Configuration::updateValue('SANDBOX_PASSWORD', Tools::getValue('SANDBOX_PASSWORD'));
            }
            Configuration::updateValue('SANDBOX_STOREIDACQ', Tools::getValue('SANDBOX_STOREIDACQ'));
            Configuration::updateValue('SANDBOX_STOREAPIKEY', Tools::getValue('SANDBOX_STOREAPIKEY'));
            Configuration::updateValue('SANDBOX_ORGID', Tools::getValue('SANDBOX_ORGID'));
            Configuration::updateValue('SANDBOX_MID', Tools::getValue('SANDBOX_MID'));
            Configuration::updateValue('SANDBOX_TRANSTYPE', Tools::getValue('SANDBOX_TRANSTYPE'));

            Configuration::updateValue('SANDBOX_MSI_000303', Tools::getValue('SANDBOX_MSI_000303'));
            Configuration::updateValue('SANDBOX_MSI_000603', Tools::getValue('SANDBOX_MSI_000603'));
            Configuration::updateValue('SANDBOX_MSI_000903', Tools::getValue('SANDBOX_MSI_000903'));
            Configuration::updateValue('SANDBOX_MSI_001203', Tools::getValue('SANDBOX_MSI_001203'));
            Configuration::updateValue('SANDBOX_MSI_001803', Tools::getValue('SANDBOX_MSI_001803'));


            Configuration::updateValue('USERNAME', Tools::getValue('USERNAME'));
            if(!empty(Tools::getValue('PASSWORD'))) {
                Configuration::updateValue('PASSWORD', Tools::getValue('PASSWORD'));
            }
            Configuration::updateValue('STOREIDACQ', Tools::getValue('STOREIDACQ'));
            Configuration::updateValue('STOREAPIKEY', Tools::getValue('STOREAPIKEY'));
            Configuration::updateValue('ORGID', Tools::getValue('ORGID'));
            Configuration::updateValue('MID', Tools::getValue('MID'));
            Configuration::updateValue('TRANSTYPE', Tools::getValue('TRANSTYPE'));

            Configuration::updateValue('MSI_000303', Tools::getValue('MSI_000303'));
            Configuration::updateValue('MSI_000603', Tools::getValue('MSI_000603'));
            Configuration::updateValue('MSI_000903', Tools::getValue('MSI_000903'));
            Configuration::updateValue('MSI_001203', Tools::getValue('MSI_001203'));
            Configuration::updateValue('MSI_001803', Tools::getValue('MSI_001803'));

            Configuration::updateValue('sandbox', Tools::getValue('sandbox'));
            Configuration::updateValue('NETPAY_IPN', Tools::getValue('NETPAY_IPN'));
        }

        $jwt = $this->login($this->sandbox, $this->username, $this->password);
        $isCashEnable = \NetPay\Api\IsCashEnable::get($this->sandbox, $jwt);
        if($isCashEnable['result']['response']) {
            Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'netpay_conf(id,pay_in_cash) 
            VALUES 
                (1,1) 
            ON DUPLICATE KEY UPDATE pay_in_cash = 1;
            ');
        }
        else {
            Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'netpay_conf(id,pay_in_cash) 
            VALUES 
                (1,0) 
            ON DUPLICATE KEY UPDATE pay_in_cash = 0;
            ');
        }
        
        $this->_html .= $this->displayConfirmation($this->l('Actualización exitosa'));
    }

    public function getConfigFieldsValues() {
        $value1 = Tools::getValue('SANDBOX_USERNAME', Configuration::get('SANDBOX_USERNAME'));
        $value2 = Tools::getValue('SANDBOX_PASSWORD', Configuration::get('SANDBOX_PASSWORD'));
        $value3 = Tools::getValue('SANDBOX_STOREIDACQ', Configuration::get('SANDBOX_STOREIDACQ'));
        $value4 = Tools::getValue('SANDBOX_STOREAPIKEY', Configuration::get('SANDBOX_STOREAPIKEY'));
        $value5 = Tools::getValue('SANDBOX_ORGID', Configuration::get('SANDBOX_ORGID'));
        $value6 = Tools::getValue('SANDBOX_MSI_000303', Configuration::get('SANDBOX_MSI_000303'));
        $value7 = Tools::getValue('SANDBOX_MSI_000603', Configuration::get('SANDBOX_MSI_000603'));
        $value8 = Tools::getValue('SANDBOX_MSI_000903', Configuration::get('SANDBOX_MSI_000903'));
        $value9 = Tools::getValue('SANDBOX_MSI_001203', Configuration::get('SANDBOX_MSI_001203'));
        $value10 = Tools::getValue('SANDBOX_MSI_001803', Configuration::get('SANDBOX_MSI_001803'));
        $value11 = Tools::getValue('SANDBOX_MID', Configuration::get('SANDBOX_MID'));
        $value12 = Tools::getValue('SANDBOX_TRANSTYPE', Configuration::get('SANDBOX_TRANSTYPE'));

        $value13 = Tools::getValue('USERNAME', Configuration::get('USERNAME'));
        $value14 = Tools::getValue('PASSWORD', Configuration::get('PASSWORD'));
        $value15 = Tools::getValue('STOREIDACQ', Configuration::get('STOREIDACQ'));
        $value16 = Tools::getValue('STOREAPIKEY', Configuration::get('STOREAPIKEY'));
        $value17 = Tools::getValue('ORGID', Configuration::get('ORGID'));
        $value18 = Tools::getValue('MSI_000303', Configuration::get('MSI_000303'));
        $value19 = Tools::getValue('MSI_000603', Configuration::get('MSI_000603'));
        $value20 = Tools::getValue('MSI_000903', Configuration::get('MSI_000903'));
        $value21 = Tools::getValue('MSI_001203', Configuration::get('MSI_001203'));
        $value22 = Tools::getValue('MSI_001803', Configuration::get('MSI_001803'));
        $value23 = Tools::getValue('MID', Configuration::get('MID'));
        $value24 = Tools::getValue('TRANSTYPE', Configuration::get('TRANSTYPE'));

        $value25 = Tools::getValue('NETPAY_IPN', _PS_BASE_URL_.__PS_BASE_URI__."modules/netpay/callback.php");
        $value26 = Tools::getValue('sandbox', Configuration::get('sandbox'));
        
        return array('SANDBOX_USERNAME' => $value1,
            'SANDBOX_PASSWORD' => $value2, 
            'SANDBOX_STOREIDACQ' => $value3, 
            'SANDBOX_STOREAPIKEY' => $value4, 
            'SANDBOX_ORGID' => $value5,
            'SANDBOX_MSI_000303' => $value6,
            'SANDBOX_MSI_000603' => $value7,
            'SANDBOX_MSI_000903' => $value8,
            'SANDBOX_MSI_001203' => $value9,
            'SANDBOX_MSI_001803' => $value10,
            'SANDBOX_MID' => $value11,
            'SANDBOX_TRANSTYPE' => $value12, 

            'USERNAME' => $value13,
            'PASSWORD' => $value14, 
            'STOREIDACQ' => $value15, 
            'STOREAPIKEY' => $value16, 
            'ORGID' => $value17,
            'MSI_000303' => $value18,
            'MSI_000603' => $value19,
            'MSI_000903' => $value20,
            'MSI_001203' => $value21,
            'MSI_001803' => $value22,
            'MID' => $value23,
            'TRANSTYPE' => $value24,

            'NETPAY_IPN' => $value25,
            'sandbox' => $value26
        );
    }
    private function displayNetPay() {
        return $this->fetch($this->local_path . 'views/templates/admin/header.tpl');
    }
    
    public function renderForm() {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('NetPay Setup'),
                    'icon' => 'icon-credit-card'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'values' => array(
                            array('label' => $this->l('Yes'), 'value' => 1, 'id' => 'sandbox_on'),
                            array('label' => $this->l('No'), 'value' => 0, 'id' => 'sandbox_off'),
                        ),
                        'is_bool' => true,
                        'class' => 't',
                        'label' => $this->l('Sandbox mode'),
                        'name' => 'sandbox',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Username'),
                        'name' => 'USERNAME',
                        'size' => 64,
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Password'),
                        'name' => 'PASSWORD',
                        'size' => 64,
                        'desc' => $this->l('Deja el campo vacío para no actualizar la contraseña.')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('StoreIdAcq'),
                        'name' => 'STOREIDACQ',
                        'size' => 64
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('StoreApiKey'),
                        'name' => 'STOREAPIKEY',
                        'size' => 64
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('OrgId'),
                        'name' => 'ORGID',
                        'size' => 64
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('MSI'),
                        'name' => 'MSI',
                        'desc' => $this->l('Selecciona los meses sin intereses.'),
                        'required' => false,
                        'values'  => array(
                            'query' => $this->getMSI(),
                            'id' => 'id',
                            'name'  => 'name',
                            'expand' => array(
                                'print_total' => count($this->getMSI()),
                                'default' => 'show',
                                'show' => array('text' => $this->l('show'), 'icon' => 'plus-sign-alt'),
                                'hide' => array('text' => $this->l('hide'), 'icon' => 'minus-sign-alt')
                             ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('MID'),
                        'name' => 'MID',
                        'desc' => $this->l('Selecciona el MID adecuado a tu negocio.'),
                        'required' => false,
                        'options'  => array(
                            'query' => $this->getMids(),
                            'id' => 'id',
                            'name'  => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Transtype'),
                        'name' => 'TRANSTYPE',
                        'desc' => $this->l('Selecciona el tipo de transacción adecuado a tu negocio.'),
                        'required' => false,
                        'options'  => array(
                            'query' => $this->getTranstype(),
                            'id' => 'id',
                            'name'  => 'name'
                        ),
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox username'),
                        'name' => 'SANDBOX_USERNAME',
                        'size' => 64,
                        'required' => true
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('Sandbox password'),
                        'name' => 'SANDBOX_PASSWORD',
                        'size' => 64,
                        'required' => true,
                        'desc' => $this->l('Deja el campo vacío para no actualizar la contraseña.')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox storeIdAcq'),
                        'name' => 'SANDBOX_STOREIDACQ',
                        'size' => 64,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox storeApiKey'),
                        'name' => 'SANDBOX_STOREAPIKEY',
                        'size' => 64,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox orgId'),
                        'name' => 'SANDBOX_ORGID',
                        'size' => 64,
                        'required' => true
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Sandbox MSI'),
                        'name' => 'SANDBOX_MSI',
                        'desc' => $this->l('Selecciona los meses sin intereses.'),
                        'required' => true,
                        'values'  => array(
                            'query' => $this->getMSI(),
                            'id' => 'id',
                            'name'  => 'name',
                            'expand' => array(
                                'print_total' => count($this->getMSI()),
                                'default' => 'show',
                                'show' => array('text' => $this->l('show'), 'icon' => 'plus-sign-alt'),
                                'hide' => array('text' => $this->l('hide'), 'icon' => 'minus-sign-alt')
                             ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Sandbox MID'),
                        'name' => 'SANDBOX_MID',
                        'desc' => $this->l('Selecciona el MID adecuado a tu negocio.'),
                        'required' => true,
                        'options'  => array(
                            'query' => $this->getMids(),
                            'id' => 'id',
                            'name'  => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Sandbox Transtype'),
                        'name' => 'SANDBOX_TRANSTYPE',
                        'desc' => $this->l('Selecciona el tipo de transacción adecuado a tu negocio.'),
                        'required' => true,
                        'options'  => array(
                            'query' => $this->getTranstype(),
                            'id' => 'id',
                            'name'  => 'name'
                        ),
                    ),


                    array(
                        'type' => 'text',
                        'label' => $this->l('Payments notification URL (IPN):'),
                        'name' => 'NETPAY_IPN',
                        'disabled' => true,
                        'desc' => $this->l('URL callback en donde se verificará si el pago fué realizado.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $value1 = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $value2 = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->allow_employee_form_lang = $value1 ? $value2 : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $value3 = '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).$value3;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
                'fields_value' => $this->getConfigFieldsValues(),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getMids() {
        return array (
            array (
                'id' => 'netpaymx_retail',
                'name' => 'Retail'),
            array (
                'id' => 'netpaymx_schools',
                'name' => 'Escuelas'),
            array (
                'id' => 'netpaymx_donativos',
                'name' => 'Donativos'),
            array (
                'id' => 'netpaymx_services',
                'name' => 'Servicios Generales'),
            array (
                'id' => 'netpaymx_tickets',
                'name' => 'Eventos'),
            array (
                'id' => 'netpaymx_food',
                'name' => 'Restaurantes'),
        );
    }

    public function getMSI() {
        return array (
            array (
                'id' => '000303',
                'val' => '000303',
                'name' => '3 Meses sin intereses'),
            array (
                'id' => '000603',
                'val' => '000603',
                'name' => '6 Meses sin intereses'),
            array (
                'id' => '000903',
                'val' => '000903',
                'name' => '9 Meses sin intereses'),
            array (
                'id' => '001203',
                'val' => '001203',
                'name' => '12 Meses sin intereses'),
            array (
                'id' => '001803',
                'val' => '001803',
                'name' => '18 Meses sin intereses'),
        );
    }

    public function getTranstype() {
        return array (
            array (
                'id' => 'Auth',
                'name' => 'Auth'),
            array (
                'id' => 'PreAuth',
                'name' => 'PreAuth/PostAuth'),
        );
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                        $this->_html .= $this->displayError($err);
                }
            }
        }

        $this->_html .= $this->displayNetPay();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }
/***************************************************************************************************************************************************/
/*******************************************************************CATALOG*************************************************************************/
    public function hookPaymentOptions() {
        if (!$this->active) {
            return;
        }

        for($i=0 ; $i<10 ; $i++)
        {
            $expiration_years[] = array(
                'description' => date('Y', strtotime('+'.$i.' year')),
                'year' => substr(date('Y', strtotime('+'.$i.' year')), 2, 4)
            );
        }

        $deviceFingerprintID = $this->context->cart->secure_key.date('YmdHis');

        $this->msi_000303 = $this->msi_by_amount(Tools::safeOutput(Configuration::get($this->mode . 'MSI_000303')), $this->context->cart->getOrderTotal());
        $this->msi_000603 = $this->msi_by_amount(Tools::safeOutput(Configuration::get($this->mode . 'MSI_000603')), $this->context->cart->getOrderTotal());
        $this->msi_000903 = $this->msi_by_amount(Tools::safeOutput(Configuration::get($this->mode . 'MSI_000903')), $this->context->cart->getOrderTotal());
        $this->msi_001203 = $this->msi_by_amount(Tools::safeOutput(Configuration::get($this->mode . 'MSI_001203')), $this->context->cart->getOrderTotal());
        $this->msi_001803 = $this->msi_by_amount(Tools::safeOutput(Configuration::get($this->mode . 'MSI_001803')), $this->context->cart->getOrderTotal());

        $mid_value = Tools::safeOutput(Configuration::get($this->mode . 'MID'));

        $arguments = array(
            'username'         => Tools::safeOutput(Configuration::get($this->mode . 'USERNAME')),
            'expiration_years'  => $expiration_years,
            'msi_000303'  => $this->msi_000303,
            'msi_000603'  => $this->msi_000603,
            'msi_000903'  => $this->msi_000903,
            'msi_001203'  => $this->msi_001203,
            'msi_001803'  => $this->msi_001803,
            'mid'  => $mid_value,
            'total'             => $this->context->cart->getOrderTotal(),
            'currency'          => $this->context->currency->iso_code,
            'cart_id'           => $this->context->cart->id,
            'customer_id'       => $this->context->cart->id_customer,
            'secure_key'        => $this->context->cart->secure_key,
            'platform'          => "prestashop",
            'deviceFingerprintID'   => $deviceFingerprintID,
            'orgId'             => Tools::safeOutput(Configuration::get($this->mode . 'ORGID')),
            'platform_version'  => _PS_VERSION_,
            'return_url'        => $this->context->link->getBaseLink()."modules/netpay/validation.php"
        );

        $this->context->smarty->assign($arguments);

        /*CLEAN TEMPLATE (payment.tpl) CACHE */
        $this->context->smarty->compile_check = true;
        $this->context->smarty->force_compile = true;
        $this->context->smarty->clearCompiledTemplate();

        $cardOption = new PaymentOption();
        $cardOption->setModuleName($this->name)
            ->setCallToActionText($this->trans($this->l('Pagos con tarjetas de crédito y débito'), array(), 'Modules.NetPay.Shop'))
            //->setAction($this->context->link->getModuleLink('netpay', 'validation', array(), true))
            ->setAdditionalInformation($this->fetch('module:netpay/views/templates/front/payment.tpl'));

        $is_cash_enable = $this->is_cash_enable();
        if($is_cash_enable == 1) {
            $cashOption = new PaymentOption();
            $cashOption->setModuleName($this->name)
                ->setCallToActionText($this->trans($this->l('Pagos en efectivo'), array(), 'Modules.NetPay.Cash'))
                //->setAction($this->context->link->getModuleLink('netpay', 'validation', array(), true))
                ->setAdditionalInformation($this->fetch('module:netpay/views/templates/front/cash.tpl'));

                $payment_options = array(
                    $cardOption,
                    $cashOption
                );
        }
        else {
            $payment_options = array(
                $cardOption
            );
        }
    
        return $payment_options;
    }

    public function hookactionOrderStatusPostUpdate($params) {
        $object = json_decode(json_encode($params), true);
        $orderObject = new Order();
        $id_order = (int) $object['id_order'];
        unset($orderObject);
        if ($id_order > 0) {
            $orderObject = new Order($id_order);
            $netPayOrder = $this->getNetPayOrder($id_order);
            $transactionTokenId = $netPayOrder['transaction_token_id'];
            if ((int) Configuration::get('NETPAY_ORDER_PROCESSING') == $orderObject->current_state &&
            $netPayOrder['preauth'] == 1) {
                $transactionType = 'PostAuth'; //Auth, PreAuth, PostAuth
                $jwt = $this->login($this->sandbox, $this->username, $this->password);
                $charge = \NetPay\Api\Charge::post($this->sandbox, $jwt, $transactionTokenId, '', $transactionType);
                if ($charge['result']['transaction']['status'] == 'DONE') {
                    $this->context->controller->success[] = 'El cambio de estatus en NetPay se ha realizado correctamente.';
                    return true;
                }
                else {
                    $this->context->controller->errors[] = 'Error al cambiar el estatus en NetPay - ' . $charge['result']['responseMsg'] ;
                    return false;
                }
            }
            else if ((int) Configuration::get('NETPAY_ORDER_CANCELLED') == $orderObject->current_state &&
                $netPayOrder['reference'] == 0) {
                if(!$this->is_today_day_paid($netPayOrder)) {
                    $this->context->controller->errors[] = 'Sólo se permite cancelar órdenes el mismo día en que se pagó.';
                    return false;
                }
                else {
                    $data = array(
                        'transaction_token_id' => $transactionTokenId
                    );
                    $jwt = $this->login($this->sandbox, $this->username, $this->password);
                    $cancel = \NetPay\Api\Cancelled::post($this->sandbox, $jwt, $data);
                    if (isset($cancel['result']['response']) && $cancel['result']['response']['responseCode'] != '00') {
                        $this->context->controller->success[] = 'El cambio de estatus en NetPay se ha realizado correctamente.';
                        return true;
                    }
                    else {
                        $this->context->controller->errors[] = 'No se pudo cancelar la órden.';
                        return false;
                    }
                }
            }
            else if ((int) Configuration::get('NETPAY_ORDER_CANCELLED') == $orderObject->current_state &&
                $netPayOrder['reference'] == 1) {
                    $this->context->controller->errors[] = 'No se permite cancelar órdenes pagadas en efectivo.';
                    return false;
                }
        }
        return true;
    }

    private function is_today_day_paid($netPayOrder) {
        return date("Ymd", strtotime($netPayOrder['register_date'])) == date("Ymd");
    }

    private function login($sandbox, $username, $password) {
        $data = array(
            'userName' => $username,
            'password' => $password,
        );
        $login = \NetPay\Api\Login::post($sandbox, $data);
        return $login['result']['token'];
    }

    public function getNetPayOrder($id_order) {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'netpay_order` WHERE `id_order` = \''.(int)$id_order.'\'');
    }

    public function getOrderByCart($id_cart) {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'netpay_order` WHERE `id_cart` = \''.(int)$id_cart.'\'');
    }

    public function msi_by_amount($msi, $amount) {
        switch($msi) {
            case '000303':
                return ($amount<300 || $this->transtype == 'PreAuth') ? null : $msi;
            break;
            case '000603':
                return ($amount<600 || $this->transtype == 'PreAuth') ? null : $msi;
            break;
            case '000903':
                return ($amount<900 || $this->transtype == 'PreAuth') ? null : $msi;
            break;
            case '001203':
                return ($amount<1200 || $this->transtype == 'PreAuth') ? null : $msi;
            break;
            case '001803':
                return ($amount<1800 || $this->transtype == 'PreAuth') ? null : $msi;
            break;
            default:
                return null;
        }
    }

    // Used by netpay_ipn.php
    public function validationNetPay() {
        $usr      =    trim(Tools::getValue('username'));
        $pass      =    trim(Tools::getValue('password'));
        $price          =    trim(Tools::getValue('price'));
        $frmPrice       =    trim(Tools::getValue('frmprice'));
        $frmCurrency    =    trim(Tools::getValue('frmcurrency'));
        $custom         =    trim(Tools::getValue('custom'));
        $arrayCustom    =    explode("-", $custom);
        $cartID         =    trim($arrayCustom[0]);
        $customerID     =    trim($arrayCustom[1]);
        $userSecureKey  =    trim($arrayCustom[2]);
        $PS_Username   =    trim(Tools::safeOutput(Configuration::get($this->mode . 'USERNAME')));
        $PS_Password   =    trim(Tools::safeOutput(Configuration::get($this->mode . 'PASSWORD')));

        if ($PS_Username != $usr || empty($usr)) {
            exit("Error: Wrong Username");
        }
        if ($PS_Password != $pass || empty($pass)) {
            exit("Error: Wrong password");
        }
        if (empty($price) || empty($frmPrice) || empty($frmCurrency)) {
            exit("Error: Wrong order values");
        }
        if (count($arrayCustom) < 3 || Tools::strlen($cartID) < 1 ||
            Tools::strlen($customerID) < 1 || Tools::strlen($userSecureKey) < 1) {
            exit("Error: Wrong custom values");
        }
        if (Validate::isLoadedObject(new Cart($cartID))) {
            $ObjCart = new Cart($cartID);
        } else {
            exit("Error: Cart does not exist");
        }

        $priceValidation = $this->validateTotalPaid($cartID, $frmPrice, $frmCurrency);

        if ($ObjCart->orderExists() > 0 && $priceValidation === true) {
            $OrderObject = new Order();
            $OrderObject = new Order($OrderObject->getOrderByCartId($ObjCart->id));
            
            if ($userSecureKey != $OrderObject->secure_key || $userSecureKey != $ObjCart->secure_key) {
                exit("Error: Secure key does not match");
            }
            
            if ($customerID != $OrderObject->id_customer || $customerID != $ObjCart->id_customer) {
                exit("Error: Customer ID does not match");
            }

            if ((int)Configuration::get('NETPAY_ORDER_SUCCESS') == $OrderObject->current_state) {
                exit("IPN CALLBACK: ALREADY COMPLETED");
            } else {
                $OrderObject->setCurrentState((int)Configuration::get('NETPAY_ORDER_SUCCESS'));
                exit("IPN CALLBACK: OK");
            }
        }
    }//validationNetPay()
    
    /**********************************TOOLS**********************************/
    public function validateTotalPaid($cartID, $frmPrice, $frmCurrency) {

        $orderObject = new Order();
        $orderID = $orderObject->getOrderByCartId((int)$cartID);
        unset($orderObject);
        
        $orderObject = new Order($orderID);
        $totalPaid = $orderObject->total_paid;
        
        $currencyObject = new Currency($orderObject->id_currency);
        $currencyISOCode = $currencyObject->iso_code;
        
        $response = false;
        if ($frmCurrency == $currencyISOCode && $frmPrice == $totalPaid) {
            $response = true;
        } else {
            exit("CALLBACK ERROR: CHECK YOUR IPN AND THE AMOUNT FOR THIS TRANSACTION");
        }
        
        return ($response);
    }

    /**
     * Load JS on the front office order page
     */
    public function hookHeader() {
        $this->context->controller->registerJavascript(
            $this->name.'-payments',
            'modules/'.$this->name.'/views/js/payments.js'
       ); 

        // Javacript variables needed by Elements
        Media::addJsDef(array(
            'validation_return_url' => Configuration::get('NETPAY_IPN')
        ));
    }

    function is_cash_enable() {
        $response = Db::getInstance()->getRow('SELECT pay_in_cash FROM `'._DB_PREFIX_.'netpay_conf` WHERE `id` = 1 ; ');
        return  $response['pay_in_cash'];
    }

    /**
     * Hook Order Confirmation
     */
    public function hookOrderConfirmation($params)
    {
        $object = json_decode(json_encode($params), true);
        $orderObject = new Order();
        $id_cart = (int) $object['order']['id_cart'];
        unset($orderObject);
        if ($id_cart > 0) {
            $netPayOrder = $this->getOrderByCart($id_cart);
            $isReference = $netPayOrder['is_reference'];
            $reference = $netPayOrder['reference'];

            if($isReference == 1) {
                $this->context->smarty->assign(array(
                    'reference' => $reference,
                    'isReference' => $isReference,
                    'site' => Configuration::get('PS_SHOP_NAME'),
                    'paymentsOption' => "../modules/netpay/views/img/tiendas.svg"
                ));
            }
        }

        return $this->display(__FILE__, 'views/templates/front/order-confirmation.tpl');
    }

}
