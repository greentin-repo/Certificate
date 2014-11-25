<?php

require_once(dirname(dirname(__FILE__)) . '/Setting/srCertificateSetting.php');

/**
 * srCertificateTypeSetting
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version
 */
class srCertificateTypeSetting extends ActiveRecord implements srCertificateSetting
{

    /**
     * MySQL Table-Name
     */
    const TABLE_NAME = 'cert_type_setting';

    const IDENTIFIER_DEFAULT_LANG = 'default_lang';
    const IDENTIFIER_VALIDITY_TYPE = 'validity_type';
    const IDENTIFIER_VALIDITY = 'validity';
    const IDENTIFIER_GENERATION = 'generation';
    const IDENTIFIER_NOTIFICATION = 'notification';
    const IDENTIFIER_NOTIFICATION_USER = 'notification_user';
    const IDENTIFIER_DOWNLOADABLE = 'downloadable';

    const VALIDITY_TYPE_ALWAYS = 1;
    const VALIDITY_TYPE_DATE_RANGE = 2;
    const VALIDITY_TYPE_DATE = 3;

    const GENERATION_AUTO = 'auto';
    const GENERATION_MANUAL = 'manual';


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     * @db_is_primary   true
     * @db_sequence     true
     */
    protected $id = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       8
     */
    protected $type_id;


    /**
     * @var string
     *
     * @db_has_field    true
     * @db_fieldtype    text
     * @db_length       256
     */
    protected $identifier;


    /**
     * @var array
     *
     * @db_has_field    true
     * @db_fieldtype    text
     * @db_length       1204
     */
    protected $editable_in = '';


    /**
     * @var string
     *
     * @db_has_field    true
     * @db_fieldtype    text
     * @db_length       1204
     */
    protected $default_value;


    public function __construct($id = 0)
    {
        parent::__construct($id);
    }


    // Public

    /**
     * Set values after reading from DB, e.g. convert from JSON to Array
     *
     * @param $key
     * @param $value
     * @return mixed|null
     */
    public function wakeUp($key, $value)
    {
        switch ($key) {
            case 'editable_in':
                $value = json_decode($value, true);
                break;
        }
        return $value;
    }


    /**
     * Set values before saving to DB
     *
     * @param $key
     * @return int|mixed|string
     */
    public function sleep($key)
    {
        $value = $this->{$key};
        switch ($key) {
            case 'editable_in':
                $value = json_encode($value);
                break;
        }
        return $value;
    }


    // Static


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    // Getters & Setters


    /**
     * @param string $default_value
     */
    public function setDefaultValue($default_value)
    {
        $this->setValue($default_value);
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param array $editable_in
     */
    public function setEditableIn($editable_in)
    {
        $this->editable_in = $editable_in;
    }

    /**
     * @return array
     */
    public function getEditableIn()
    {
        return $this->editable_in;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param int $type_id
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->type_id;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param $validity_type
     * @param $value
     * @return mixed
     */
    public static function formatValidityBasedOnType($validity_type, $value)
    {
        switch ($validity_type) {
            case srCertificateTypeSetting::VALIDITY_TYPE_ALWAYS:
                $value = "";
                break;
            case srCertificateTypeSetting::VALIDITY_TYPE_DATE:
                $value = (isset($value['date'])) ? date('Y-m-d', strtotime($value['date'])) : '';
                break;
            case srCertificateTypeSetting::VALIDITY_TYPE_DATE_RANGE:
                if (is_array($value) && isset($value['dd']) && isset($value['MM'])) {
                    $value = json_encode(array('d' => $value['dd'], 'm' => $value['MM']));
                } else {
                    $value = '';
                }
                break;
        }

        return $value;
    }


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        // This should be factored out, currently there is one exception where a value needs to be parsed before storing in DB
        if ($value && $this->getIdentifier() == srCertificateTypeSetting::IDENTIFIER_VALIDITY) {
            /** @var srCertificateType $type */
            $type = srCertificateType::find($this->getTypeId());
            $value = self::formatValidityBasedOnType($type->getSettingByIdentifier(self::IDENTIFIER_VALIDITY_TYPE)->getValue(), $value);
        }

        $this->default_value = $value;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getDefaultValue();
    }
}
