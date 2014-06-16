<?php
/**
 * DB Connection class
 *
 * @filesource
 * @package    motte
 * @subpackage model
 * @version    1.0
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pedro Gauna (pgauna@gmail.com) /
 *             Carlos Gagliardi (carlosgag@gmail.com) /
 *             Braulio Rios (braulioriosf@gmail.com) /
 *             Pablo Erartes (pabloeuy@gmail.com) /
 *             GBoksar/Perro (gustavo@boksar.info)
 * @link       http://motte.codigolibre.net Motte Website
 */
    abstract class mteCnx {

        /**
         *  IP Address of DB Server
         *
         * @access protected
         * @var  string
         */
        protected $hostName;

        /**
         *
         *
         * @access protected
         * @var string
         */
        protected $userName;

        /**
         *
         *
         * @access protected
         * @var  string
         */
        protected $password;

        /**
         *
         *
         * @access protected
         * @var string
         */
        protected $baseName;

        /**
         *
         *
         * @access protected
         * @var string
         */
        protected $port;

        /**
         *
         * @access protected
         * @var  boolean
         */
        protected $persistent;

        /**
         *  Mensaje del evento
         *
         * @access protected
         * @var string
         */
        protected $eventoMsg;

        /**
         *  Mensaje del evento
         *
         * @access protected
         * @var int
         */
        protected $eventoCodigo;

        /**
         *  Id de conexion de la base de datos
         *
         * @access protected
         * @var resource
         */
        private $_idDatabase;

        /**
         * On/Off Debug
         * @access protected
         * @var boolean
         */
        protected $debug;

        /**
         * On/Off Debug
         * @access protected
         * @var boolean
         */
        protected $charset;


        /**
         * Constructor
         *
         * @access public
         * @return mteCnx
         */
        public function __construct() {
            $this->initialize();
        }

       /**
        * Destructor
        *
        * @access public
        */
        public function __destruct(){
        }

        /**
         * Initialize class attributes
         *
         * @access public
         * @return void
         */
        public function initialize(){
            $this->setHost();
            $this->setBaseName();
            $this->setPass();
            $this->setUser();
            $this->setEventCode(-1);
            $this->setEventMsg();
            $this->setIdDatabase();
            $this->setPersistent(false);
            $this->setDebug();
            $this->setPort();
            $this->setCharset('utf8');
        }

        /**
         *
         *
         * @access public
         * @param string $host
         * @return void
         */
        public function setHost($hostName = ''){
            $this->hostName = $hostName;
        }

        /**
         *
         *
         * @access public
         * @param string $host
         * @return void
         */
        public function setPort($port = ''){
            $this->port = $port;
        }

        /**
         *
         *
         * @access public
         * @param string $user
         * @return void
         */
        public function setUser($userName = ''){
            $this->userName = $userName;
        }

        /**
         *
         *
         * @access public
         * @param string $pass
         * @return void
         */
        public function setPass($password = ''){
            $this->password = $password;
        }

        /**
         *
         *
         * @access public
         * @param string $basename
         * @return void
         */
        public function setBaseName($baseName = ''){
            $this->baseName = $baseName;
        }


        /**
         *
         *
         * @access public
         * @param int $eventoCodigo
         * @return void
         */
        public function setEventCode($eventoCodigo = -1){
            $this->eventoCodigo = $eventoCodigo;
        }

        /**
         *
         *
         * @access public
         * @param string $eventoMsg
         * @return void
         */
        public function setEventMsg($eventoMsg = ''){
            $this->eventoMsg = $eventoMsg;
        }

        /**
         *
         *
         * @access public
         * @param object
         * @return void
         */
        public function setIdDatabase($idDatabase = ''){
            $this->_idDatabase=$idDatabase;
        }

        /**
         *
         *
         * @access public
         * @param boolean
         * @return void
         */
        public function setPersistent($persistent = false){
            $this->persistent=$persistent;
        }

        /**
         *
         *
         * @access public
         * @param boolean
         * @return void
         */
        public function setDebug($debug = ''){
            $this->debug=$debug;
        }

       /**
         *
         *
         * @access public
         * @param string $host
         * @return void
         */
        public function setCharset($charset = ''){
            $this->charset = $charset;
        }

        /**
         *
         *
         * @access public
         * @return boolean
         */
        public function getPersistent(){
            return $this->persistent;
        }

        /**
         *
         *
         * @access public
         * @return boolean
         */
        public function getDebug(){
            return $this->debug;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getHost(){
            return $this->hostName;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getPort(){
            return $this->port;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getUser(){
            return $this->userName;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getPass(){
            return $this->password;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getBaseName(){
            return $this->baseName;
        }

       /**
         *
         *
         * @access public
         * @param string $host
         * @return void
         */
        public function getCharset(){
            return $this->charset;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return int
         */
        public function getEventCode(){
            return $this->eventoCodigo;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return string
         */
        public function getEventMsg(){
            return $this->eventoMsg;
        }

        /**
         *
         *
         * @access public
         * @param
         * @return object
         */
        public function getIdDatabase(){
            return $this->_idDatabase;
        }

        /**
         * Display debug info
         * @access 	protected
         * @param 	string		$comm
         * @return 	void
         */
        protected function displayDebug($comm = ''){
            print(nl2br($comm."\n\n"));
        }

        /**
         *
         *
         * @access protected
         * @param
         * @return
         */
        protected function checkParams(){
            return ($this->getHost() != '' && $this->getUser() != '' && $this->getPass() != '' && $this->getBaseName() != '' );
        }
    }
?>