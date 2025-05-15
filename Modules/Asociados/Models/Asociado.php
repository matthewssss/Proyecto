<?php
require_once '../../Padres/Models/Padre.php';

class Asociado {
    private $id_ins;
    private $id_usr;
    private $nombre;
    private $apellidos;
    private $DNI;
    private $fecha_nacimiento;
    private $edad;
    private $unidad;
    private $cp;
    private $municipioId;
    private $provinciaId;
    private $comunidad_autonomaId;
    private $municipioName;
    private $provinciaName;
    private $comunidad_autonomaName;
    private $padre;


    public function __construct(array $data) {
        $this->id_ins = $data['id_ins'];
        $this->id_usr = $data['id_usr'];
        $this->nombre = $data['nombre'];
        $this->apellidos = $data['apellidos'];
        $this->DNI = $data['DNI'];
        $this->fecha_nacimiento = $data['fecha_nacimiento'];
        $this->edad = $data['edad'];
        $this->unidad = $data['unidad'];
        $this->cp = $data['cp'];
        $this->municipioId = $data['municipio'];
        $this->provinciaId = $data['provincia'];
        $this->comunidad_autonomaId = $data['comunidad_autonoma'];
        $this->padre = new Padre($data);
    }

    // Getters
    public function getIdIns() {
        return $this->id_ins;
    }

    public function getIdUsr() {
        return $this->id_usr;
    }

    public function getNombre() {
        return $this->decryptField($this->nombre);
    }

    public function getApellidos() {
        return $this->decryptField($this->apellidos);
    }

    public function getDNI() {
        return $this->decryptField($this->DNI);
    }

    public function getFechaNacimiento() {
        return $this->decryptField($this->fecha_nacimiento);
    }

    public function getEdad() {
        return $this->decryptField($this->edad);
    }

    public function getUnidad() {
        return $this->unidad;
    }

    public function getCp() {
        return $this->decryptField($this->cp);
    }

    public function getMunicipioId() {
        return $this->decryptField($this->municipioId);
    }

    public function getProvinciaId() {
        return $this->decryptField($this->provinciaId);
    }

    public function getComunidadAutonomaId() {
        return $this->decryptField($this->comunidad_autonomaId);
    }

    public function getMunicipioName() {
        return $this->municipioName;
    }

    public function getProvinciaName() {
        return $this->provinciaName;
    }

    public function getComunidadAutonomaName() {
        return $this->comunidad_autonomaName;
    }



    // Setters
    public function setIdIns($id_ins) {
        $this->id_ins = $id_ins;
    }

    public function setIdUsr($id_usr) {
        $this->id_usr = $id_usr;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setApellidos($apellidos) {
        $this->apellidos = $apellidos;
    }

    public function setDNI($DNI) {
        $this->DNI = $DNI;
    }

    public function setFechaNacimiento($fecha_nacimiento) {
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    public function setEdad($edad) {
        $this->edad = $edad;
    }

    public function setUnidad($unidad) {
        $this->unidad = $unidad;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setMunicipioId($municipioId) {
        $this->municipioId = $municipioId;
    }

    public function setProvinciaId($provinciaId) {
        $this->provinciaId = $provinciaId;
    }

    public function setComunidadAutonomaId($comunidad_autonomaId) {
        $this->comunidad_autonomaId = $comunidad_autonomaId;
    }

    public function setMunicipioName($municipioName) {
        $this->municipioName = $municipioName;
    }

    public function setProvinciaName($provinciaName) {
        $this->provinciaName = $provinciaName;
    }

    public function setComunidadAutonomaName($comunidad_autonomaName) {
        $this->comunidad_autonomaName = $comunidad_autonomaName;
    }

    /* ****************************** */
    /*   PADRE GETTER                 */
    /* ****************************** */
    // Getter for Padre object
    public function getPadre() {
        return $this->padre;
    }

    // Convenience methods to access Padre properties directly
    public function getPadreId() {
        return $this->padre ? $this->padre->getId() : null;
    }

    public function getPadreNombre() {
        return $this->padre ? $this->decryptField($this->padre->getNombre()) : null;
    }

    public function getPadreApellidos() {
        return $this->padre ? $this->decryptField($this->padre->getApellidos()) : null;
    }

    public function getPadreCorreo() {
        return $this->padre ? $this->decryptField($this->padre->getCorreo()) : null;
    }

    public function getPadreTel() {
        return $this->padre ? $this->decryptField($this->padre->getTel()) : null;
    }

    public function getPadreEstadoCivil() {
        return $this->padre ? $this->decryptField($this->padre->getEstadoCivil()) : null;
    }

    public function getPadreDni() {
        return $this->padre ? $this->decryptField($this->padre->getDni()) : null;
    }


    function decryptField($encryptedField) {
        // Recoge la clave de cifrado desde el archivo .env (variable de entorno)
        $encryption_key = getenv('ENCRYPTION_KEY'); // Clave secreta almacenada en el entorno del servidor
        
        // Decodifica el texto cifrado desde Base64 a binario
        $data = base64_decode($encryptedField);
        
        // Obtiene la longitud del IV para saber cómo separar el IV del texto cifrado
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        
        // Extrae el IV del principio de los datos (tamaño específico)
        $iv = substr($data, 0, $iv_length);
        
        // Si el IV tiene una longitud incorrecta, rellenamos con ceros (si es más corto)
        if (strlen($iv) < $iv_length) {
            $iv = str_pad($iv, $iv_length, "\0");  // Rellenamos con ceros hasta que tenga 16 bytes
        }
    
        // Extrae el texto cifrado del resto de los datos (después del IV)
        $encrypted_data = substr($data, $iv_length);
        
        // Desencripta el texto cifrado usando la clave y el IV
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
    
    public function getAllData() {
        $data = [
            'asociado' => [
                'id_ins' => $this->getIdIns(),
                'id_usr' => $this->getIdUsr(),
                'nombre' => $this->getNombre(),
                'apellidos' => $this->getApellidos(),
                //'dni' => $this->getDNI(),
                'fecha_nacimiento' => $this->getFechaNacimiento(),
                'edad' => $this->getEdad(),
                'unidad' => $this->getUnidad(),
                //'cp' => $this->getCp(),
                'municipio' => $this->getMunicipioName(),
                'provincia' => $this->getProvinciaName(),
                'comunidad_autonoma' => $this->getComunidadAutonomaName(),
                'municipioId' => $this->getMunicipioId(),
                'provinciaId' => $this->getProvinciaId(),
                'comunidad_autonomaId' => $this->getComunidadAutonomaId()
            ],
            'padre' => [
                'id' => $this->getPadreId(),
                'nombre' => $this->getPadreNombre(),
                'apellidos' => $this->getPadreApellidos(),
                'correo' => $this->getPadreCorreo(),
                'telefono' => $this->getPadreTel(),
                'estado_civil' => $this->getPadreEstadoCivil(),
                //'dni' => $this->getPadreDni()
            ]
        ];

        return $data;
    }

    public function mapLocationNames(array $comunidades, array $provincias, array $municipios) {
        $this->comunidad_autonomaName = $comunidades[(int)$this->getComunidadAutonomaId()] ?? 'Desconocido';
        $this->provinciaName = $provincias[(int)$this->getProvinciaId()] ?? 'Desconocido';
        $this->municipioName = $municipios[(int)$this->getMunicipioId()] ?? 'Desconocido'; 
    }
}