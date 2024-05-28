<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/28/16
 * Time: 12:45 PM
 */

namespace  AppBundle\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Process;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class OptPostProcessor
 * @package AppBundle\PostProcessor
 */
class OptPostProcessor implements PostProcessorInterface
{
    /**
     * @var
     */
    private $nodeDir;

    /**
     * @var
     */
    private $node;

    /**
     * @var
     */
    private $tmp;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * OptPostProcessor constructor.
     * @param $nodeDir
     * @param $tmp
     */
    public function __construct(Logger $logger, $node, $nodeDir, $tmp)
    {
        $this->logger = $logger;
        $this->node = $node;
        $this->nodeDir = $nodeDir;
        $this->tmp = $tmp;

    }


    /**
     * @param BinaryInterface $binary
     * @return Binary
     */
    public function process(BinaryInterface $binary)
    {

        // get node dir
        $nodeDir = $this->nodeDir;

        // get temp folder
        $tempDir = $this->tmp;

        //  get input file
        $input = $tempDir . ($name = md5(microtime()) . '.' . $binary->getFormat());

        if ($binary instanceof FileBinaryInterface) {
            copy($binary->getPath(), $input);
        } else {
            // create dir if not exist
            if(!file_exists($tempDir)){
                mkdir($tempDir);
            }

            file_put_contents($input, $binary->getContent());
        }

        $originalSize = filesize($input);

        $command  = $this->node . ' ' . $nodeDir . 'ImageOptimiser.js'
            . ' -f ' . $input
            . ' -p ' . $binary->getFormat()
            . ' -r ' . $tempDir
            . ' -t ' . $name ;

        $process = new Process($command);
        $process->run();

        $output = $binary->getFormat() == 'png' ? $input : $tempDir . $name;

        $optimisedSize = strlen(file_get_contents($output));

        $this->logger->info($command . " optimized: " . (100 - (100 * $optimisedSize / $originalSize )) . "%");

        if(false !== strpos($process->getOutput(), 'ok')){
            $result = new Binary(file_get_contents($input), $binary->getMimeType(), $binary->getFormat());
            unlink($input);

            return $result;
        }

        return $binary;

    }
}
