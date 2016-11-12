<?php
// src/Rol/PlatformBundle/Entity/Application.php

namespace Rol\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Rol\PlatformBundle\Entity\ApplicationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Application
{
  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column(name="author", type="string", length=255)
   */
  private $author;

  /**
   * @ORM\Column(name="content", type="text")
   */
  private $content;

  /**
   * @ORM\Column(name="date", type="datetime")
   */
  private $date;

  /**
   * @ORM\ManyToOne(targetEntity="Rol\PlatformBundle\Entity\Advert", inversedBy="applications")
   * @ORM\JoinColumn(nullable=false)
   */
  private $Advert;
  
  public function __construct()
  {
    $this->date = new \Datetime();
  }

  public function getId()
  {
    return $this->id;
  }

  public function setAuthor($author)
  {
    $this->author = $author;

    return $this;
  }

  public function getAuthor()
  {
    return $this->author;
  }

  public function setContent($content)
  {
    $this->content = $content;

    return $this;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function setDate($date)
  {
    $this->date = $date;

    return $this;
  }

  public function getDate()
  {
    return $this->date;
  }

    /**
     * Set Advert
     *
     * @param \Rol\PlatformBundle\Entity\Advert $Advert
     *
     * @return Application
     */
    public function setAdvert(Advert $Advert)
    {
        $this->Advert = $Advert;

        return $this;
    }

    /**
     * Get Advert
     *
     * @return \Rol\PlatformBundle\Entity\Advert
     */
    public function getAdvert()
    {
        return $this->Advert;
    }
    
    /**
    * @ORM\PrePersist
    */
    public function increase()
    {
        $this->getAdvert()->increaseApplication();
    }

    /**
    * @ORM\PreRemove
    */
    public function decrease()
    {
        $this->getAdvert()->decreaseApplication();
    }
}
