<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Reshadman\FileSecretary\Domain\PersistableFile;
use Reshadman\FileSecretary\Domain\PersistableFileTrait;

class GenericPersistedFile implements PersistableFile, Arrayable, Jsonable, \ArrayAccess
{
    use PersistableFileTrait;

    /**
     * Array of attributes
     *
     * @var array
     */
    protected $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * The unique identifier think of it as PK
     *
     * @return string
     */
    public function getFileableIdentifier()
    {
        return $this['id'];
    }

    /**
     * Uuid of the file. Exposed to users, as we should not expose PK's or any type of ids to the user
     * event if they are not incremental (They are uuid themselves).
     *
     * @return string
     */
    public function getFileableUuid()
    {
        return $this['uuid'];
    }

    /**
     * Creation time of the file
     *
     * @return Carbon
     */
    public function getFileableCreatedAt()
    {
        return $this['created_at'];
    }

    /**
     * The time file has been updated.
     *
     * @return Carbon
     */
    public function getFileableUpdated()
    {
        return $this['updated_at'];
    }

    /**
     * One of your defined contexts in the config file.
     *
     * @return string
     */
    public function getFileableContext()
    {
        return $this['context'];
    }

    /**
     * Client's given file name.
     *
     * @return string
     */
    public function getFileableOriginalName()
    {
        return $this['original_name'];
    }

    /**
     * Our file name.
     *
     * @return string
     */
    public function getFileableFileName()
    {
        return $this['file_name'];
    }

    /**
     * Used when there are non tracked siblings are added to the file entity (Like resized images), This folder is
     * a unique folder that allows us to perform batch actions on the folder.
     *
     * @return string
     */
    public function getFileableSiblingFolder()
    {
        return $this['sibling_folder'];
    }

    /**
     * Using the same container for all of your application contexts? By defining context for each of them
     * you can control the behaviour of the each category (For example your file manager images are not resized if you need the resize functionality)
     * for something else.
     *
     * @return string
     */
    public function getFileableContextFolder()
    {
        return $this['context_folder'];
    }

    /**
     * Get fileable hash. Is used for detecting repeated files.
     *
     * @return string
     */
    public function getFileableHash()
    {
        return $this['hash'];
    }

    /**
     * Ensures that the the two files are really equal by using an additional hash.
     *
     * @return string
     */
    public function getFileableEnsuredHash()
    {
        return $this['ensured_hash'];
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}