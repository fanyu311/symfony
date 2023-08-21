<?php

namespace App\Fixtures\Providers;

class CategorieProvider
{
    public function generateTagTitle(): string
    {
        $tags = [
            'Symfony',
            'Api Rest',
            'Php',
            'Frontend',
            'Backend',
            'FullStack',
            'VueJs',
            'Javascript',
            'Sass',
            'HTML',
            'CSS',
        ];

        // return $tags[0] 最终得到的是随机的一个tableau里的一个index的内容，而不是数字
        return $tags[array_rand($tags)];
    }
}
