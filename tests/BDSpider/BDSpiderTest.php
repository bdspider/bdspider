<?php

namespace Tests\Unit;


use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use BDSpider\BDSpider;



class BDSpiderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $keyword = '剑来 伤城中文网';
        $yoso = 'https://www.sczprc.com/info_18238.html';
        $searchResult = BDSpider::search($keyword);
        dump($searchResult);
        
        $this->assertTrue($searchResult[0]['href'] == $yoso);
    }
}
