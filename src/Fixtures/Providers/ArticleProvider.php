<?php

namespace App\Fixtures\Providers;

use Faker\Factory;
use Faker\Generator;
use App\Entity\ArticleImage;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleProvider
{
    public function __construct(
        private Generator $faker
    ) {
        // usine de generation -> Factory
        $this->faker = Factory::create('fr_FR');
    }
    public function generateArticleContent(): string
    {


        // lire un fichier mettre dans un string 
        return file_get_contents('https://loripsum.net/api/10/long/headers/link/ul/dl');
    }

    public function generateArticleDate(): \DateTimeImmutable
    {
        // creeer object depuis un object datetimeimmutable
        // appeler le faker
        return \DateTimeImmutable::createFromMutable($this->faker->dateTimeThisYear());
    }

    public function uploadArticleImage(): ArticleImage
    {
        // trouver les chemin ou fichiers -> dans le fixtures/images-> les photos
        // chemin abosulut
        // dir-> dossier courent(dossier fixtures)
        // 最后合并剩下的路径 -> a la fin dans le dossier -> articles
        // *-> extensions
        $files = glob(realpath(\dirname(__DIR__) . '/Images/Articles') . '/*.*');

        $file = new File($files[array_rand($files)]);

        // nom de image simuler 
        $uploadFile = new UploadedFile($file, $file->getBaseName());

        return (new ArticleImage)
            ->setImage($uploadFile);
    }
}
