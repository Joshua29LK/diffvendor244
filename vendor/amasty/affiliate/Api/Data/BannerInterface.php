<?php

namespace Amasty\Affiliate\Api\Data;

interface BannerInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const BANNER_ID = 'banner_id';
    public const TITLE = 'title';
    public const TYPE = 'type';
    public const IMAGE = 'image';
    public const TEXT = 'text';
    public const LINK = 'link';
    public const REL_NO_FOLLOW = 'rel_no_follow';
    public const STATUS = 'status';
    /**#@-*/

    /**
     * @return int
     */
    public function getBannerId();

    /**
     * @param int $bannerId
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setBannerId($bannerId);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string|null $title
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setType($type);

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string|null $image
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setImage($image);

    /**
     * @return string|null
     */
    public function getText();

    /**
     * @param string|null $text
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setText($text);

    /**
     * @return string|null
     */
    public function getLink();

    /**
     * @param string|null $link
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setLink($link);

    /**
     * @return int
     */
    public function getRelNoFollow();

    /**
     * @param int $relNoFollow
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setRelNoFollow($relNoFollow);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Affiliate\Api\Data\BannerInterface
     */
    public function setStatus($status);
}
