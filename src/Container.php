<?php

namespace Jsonor;


class Container implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable, \Serializable {

    public $data;
    public $owner;
    public $save;
    public $changed = false;
    public $delayed = false;

    public function __construct($data, self $parent = null) {
        $this->data   = $data;
        if($parent) {
            $this->changed = &$parent->changed;
            $this->owner = $parent->owner;
        } else {
            $this->owner = $this;
        }
    }

    /**
     * Set callback for changes
     * @param callable $callback
     * @param bool $delayed if true - callback will be called immediately otherwise in the destructor
     *
     * @return $this
     */
    public function onChange(callable $callback, $delayed = false) {
        $this->save = $callback;
        $this->delayed = $delayed;
        return $this;
    }

    /**
     * Call save callback immediately
     * @param bool $force
     *
     * @return $this
     */
    public function save($force = false) {
        if($this->owner->save && ($this->changed || $force)) {
            call_user_func($this->owner->save, $this->owner);
            $this->changed = false;
        }
        return $this;
    }

    /**
     * Return the current element
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current() {
        return $this[key($this->data)];
    }

    /**
     * Move forward to next element
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next() {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return key($this->data) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        reset($this->data);
    }

    /**
     * Whether a offset exists
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.</p>
     *
     * @return boolean true on success or false on failure.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        if(!isset($this->data[$offset])) {
            return null;
        }
        if(is_array($this->data[$offset])){
            $this->data[$offset] = new static($this->data[$offset], $this);
        }
        return $this->data[$offset];
    }

    private function modified() {
        $this->changed = true;
        if(!$this->delayed) {
            $this->save();
        }
    }

    /**
     * Offset to set
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value) {
        if($offset === null) {
            if(is_array($value)){
                $this->data[] = new static($value, $this);
            } else {
                $this->data[] = $value;
            }
        } else {
            if(is_array($value)){
                $this->data[$offset] = new static($value, $this);
            } else {
                $this->data[$offset] = $value;
            }
        }
        $this->modified();
    }

    /**
     * Offset to unset
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
        $this->modified();
    }

    /**
     * String representation of object
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize() {
        return json_encode($this->data);
    }

    /**
     * Constructs the object
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized) {
        $this->data = json_decode($serialized);
    }

    /**
     * Count elements of an object
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        return count($this->data);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return $this->data;

    }

    public function __debugInfo() {
        return ["data" => $this->data];
    }

    public function __toString() {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function __destruct() {
        if($this->delayed) {
            $this->save();
        }
    }

}