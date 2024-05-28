<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 2/16/16
 * Time: 4:35 PM
 */
namespace AppBundle\LiipFilters;

use Imagine\Filter\Basic\Crop;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\RelativeResize;

class CentralCropFilterLoader implements LoaderInterface
{
    /**
     * @param ImageInterface $image
     * @param array $options
     * @return ImageInterface.
     */
    public function load(ImageInterface $image, array $options = array())
    {
        list($width, $height) = $options['size'];
        $size = $image->getSize();

        if ($width / $height < $size->getWidth() / $size->getHeight()){
            $filter = new RelativeResize('heighten', $height);
        }
        else {
            $filter = new RelativeResize('widen', $width);
        }

        $filter->apply($image);
        $size = $image->getSize();

        $x = ($size->getWidth() - $width) / 2;
        $filter = new Crop(new Point($x, 0), new Box($width, $height));

        return $filter->apply($image);
    }

}