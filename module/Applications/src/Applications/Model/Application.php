<?php

namespace Applications\Model;

use Core\Model\AbstractModel;

/**
 * @todo write interface
 * @author mathias
 *
 */
class Application extends AbstractModel
{
    protected $jobId;
    protected $title;
    protected $firstname;
    protected $lastname;
    protected $street;
    protected $houseNumber;
    protected $zipCode;
    protected $location;
    protected $phoneNumber;
    protected $mobileNumber;
    protected $email;

    /**
     * @return the $jobId
     */
    public function getJobId ()
    {
        return $this->jobId;
    }

	/**
     * @param field_type $jobId
     */
    public function setJobId ($jobId)
    {
        $this->jobId = $jobId;
    }

	/**
     * @return the $title
     */
    public function getTitle ()
    {
        return $this->title;
    }

	/**
     * @param field_type $title
     */
    public function setTitle ($title)
    {
        $this->title = $title;
    }

	/**
     * @return the $firstname
     */
    public function getFirstname ()
    {
        return $this->firstname;
    }

	/**
     * @param field_type $firstname
     */
    public function setFirstname ($firstname)
    {
        $this->firstname = $firstname;
    }

	/**
     * @return the $lastname
     */
    public function getLastname ()
    {
        return $this->lastname;
    }

	/**
     * @param field_type $lastname
     */
    public function setLastname ($lastname)
    {
        $this->lastname = $lastname;
    }

	/**
     * @return the $street
     */
    public function getStreet ()
    {
        return $this->street;
    }

	/**
     * @param field_type $street
     */
    public function setStreet ($street)
    {
        $this->street = $street;
    }

	/**
     * @return the $housenumber
     */
    public function getHouseNumber ()
    {
        return $this->houseNumber;
    }

	/**
     * @param field_type $housenumber
     */
    public function setHouseNumber ($housenumber)
    {
        $this->houseNumber = $housenumber;
    }

	/**
     * @return the $zipCode
     */
    public function getZipCode ()
    {
        return $this->zipCode;
    }

	/**
     * @param field_type $zipCode
     */
    public function setZipCode ($zipCode)
    {
        $this->zipCode = $zipCode;
    }

	/**
     * @return the $location
     */
    public function getLocation ()
    {
        return $this->location;
    }

	/**
     * @param field_type $location
     */
    public function setLocation ($location)
    {
        $this->location = $location;
    }

	/**
     * @return the $phoneNumber
     */
    public function getPhoneNumber ()
    {
        return $this->phoneNumber;
    }

	/**
     * @param field_type $phoneNumber
     */
    public function setPhoneNumber ($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

	/**
     * @return the $mobileNumber
     */
    public function getMobileNumber ()
    {
        return $this->mobileNumber;
    }

	/**
     * @param field_type $mobileNumber
     */
    public function setMobileNumber ($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    }

	/**
     * @return the $email
     */
    public function getEmail ()
    {
        return $this->email;
    }

	/**
     * @param field_type $email
     */
    public function setEmail ($email)
    {
        $this->email = $email;
    }

    
}