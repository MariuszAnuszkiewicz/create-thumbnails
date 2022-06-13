<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use App\Services\AmazonS3ClientService;

class CreateThumbnails extends Command
{
    private const MAX_WIDTH = 150;
    protected static $defaultName = 'app:create-thumb';
    private $params;
    private $s3amazon;
    private $filesystem;
    private $finder;
    private $imagine;

    public function __construct(AmazonS3ClientService $amazonS3ClientService)
    {
        $this->s3amazon = $amazonS3ClientService;
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
        $this->imagine = new Imagine();
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Send images to external source by console.')
            ->setHelp("This command allows send images to external disk and before resize it.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $photoDir = $this->s3amazon->params->get('photo_dir');
        if (!$this->filesystem->exists($photoDir)) {
            $this->filesystem->mkdir($photoDir, 0700);
        }

        $files = $this->finder->files()->in($photoDir, false);

        foreach ($files as $file) {
            if (preg_match("(\.jpg$|\.png$|\.gif$)", $file->getFilename())) {
                $this->resizeFile($file);
                $output->writeln('resize file ' . $file->getFilename() . ' is successfully.');
                $this->s3amazon->putFiles($file);
            } else {
                $output->writeln('file extension is wrong.');
                die();
            }
        }
        return Command::SUCCESS;
    }

    private function resizeFile(string $filename): void
    {
        [$iwidth, $iheight] = getimagesize($filename);
        $ratio = $iwidth / $iheight;
        $width = self::MAX_WIDTH;
        $height = $width * 0.75;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $photo = $this->imagine->open($filename);
        $photo->resize(new Box($width, $height))->save($filename);
    }
}