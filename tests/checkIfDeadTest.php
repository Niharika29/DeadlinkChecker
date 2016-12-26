<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Wikimedia\DeadlinkChecker\CheckIfDead;

class CheckIfDeadTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test Links
	 *
	 * @param string $url URL
	 * @param bool $expect Expected link status
	 * @dataProvider provideIsLinkDead
	 */
	public function testIsLinkDead( $url, $expect ) {
		$obj = new CheckIfDead();
		$this->assertSame( $expect, $obj->isLinkDead( $url ) );
	}

	public function provideIsLinkDead() {
		// @codingStandardsIgnoreStart Line exceeds 100 characters
		$tests = [
			[ 'https://en.wikipedia.org', false ],
			[ '//en.wikipedia.org/wiki/Main_Page', false ],
			[ 'https://en.wikipedia.org/w/index.php?title=Republic_of_India', false ],
			[ 'ftp://ftp.rsa.com/pub/pkcs/ascii/layman.asc', false ],
			[ 'http://www.discogs.com/Various-Kad-Jeknu-Dragačevske-Trube-2/release/1173051', false ],
			[ 'https://astraldynamics.com', false ],
			[
				'http://napavalleyregister.com/news/napa-pipe-plant-loads-its-final-rail-car/article_695e3e0a-8d33-5e3b-917c-07a7545b3594.html',
				false
			],
			[ 'http://content.onlinejacc.org/cgi/content/full/41/9/1633', false ],
			[ 'http://flysunairexpress.com/#about', false ],
			[ 'http://www.palestineremembered.com/download/VillageStatistics/Table%20I/Haifa/Page-047.jpg', false ],
			[ 'http://list.english-heritage.org.uk/resultsingle.aspx?uid=1284140', false ],
			[ 'http://archives.lse.ac.uk/TreeBrowse.aspx?src=CalmView.Catalog&field=RefNo&key=RICHARDS', false ],
			[ 'https://en.wikipedia.org/w/index.php?title=Wikipedia:Templates_for_discussion/Holding%20cell&action=edit', false ],

			[ 'https://en.wikipedia.org/nothing', true ],
			[ '//en.wikipedia.org/nothing', true ],
			[ 'http://worldchiropracticalliance.org/resources/greens/green4.htm', true ],
			[ 'http://www.copart.co.uk/c2/specialSearch.html?_eventId=getLot&execution=e1s2&lotId=10543580', true ],
			[ 'http://forums.lavag.org/Industrial-EtherNet-EtherNet-IP-t9041.html', true ],
			[
				'http://203.221.255.21/opacs/TitleDetails?displayid=137394&collection=all&displayid=0&fieldcode=2&from=BasicSearch&genreid=0&ITEMID=$VARS.getItemId()&original=$VARS.getOriginal()&pageno=1&phrasecode=1&searchwords=Lara%20Saint%20Paul%20&status=2&subjectid=0&index=',
				true
			],
		];
		// @codingStandardsIgnoreEnd
		if ( function_exists( 'idn_to_ascii' ) ) {
			$tests[] = [ 'http://кц.рф/ru/', false ];
		}

		return $tests;
	}

	/**
	 * Test an array of dead links
	 */
	public function testAreLinksDead() {
		$obj = new CheckIfDead();
		$urls = [
			'https://en.wikipedia.org/wiki/Main_Page',
			'https://en.wikipedia.org/nothing',
		];
		$result = $obj->areLinksDead( $urls );
		$expected = [ false, true ];
		$this->assertEquals( $expected, array_values( $result ) );
	}

	/**
	 * Test the URL cleaning function
	 *
	 * @param string $url URL
	 * @param expect $expect Expected cleaned URL
	 * @dataProvider provideCleanURL
	 */
	public function testCleanURL( $url, $expect ) {
		$obj = new CheckIfDead();
		$this->assertEquals( $expect, $obj->cleanURL( $url ) );
	}

	public function provideCleanURL() {
		return [
			[ 'http://google.com?q=blah', 'google.com?q=blah' ],
			[ 'https://www.google.com/', 'google.com' ],
			[ 'ftp://google.com/#param=1', 'google.com' ],
			[ '//google.com', 'google.com' ],
			[ 'www.google.www.com', 'google.www.com' ],
		];
	}

	/**
	 * Test the URL sanitizing function
	 *
	 * @param string $url URL
	 * @param expect $expect Expected sanitized URL
	 * @dataProvider provideSanitizeURL
	 */
	public function testSanitizeURL( $url, $expect ) {
		$obj = new CheckIfDead();
		$this->assertEquals( $expect, $obj->sanitizeURL( $url ) );
	}

	public function provideSanitizeURL() {
		// @codingStandardsIgnoreStart Line exceeds 100 characters
		$tests = [
			[ 'http://google.com?q=blah', 'http://google.com/?q=blah' ],
			[ '//google.com?q=blah', 'https://google.com/?q=blah' ],
			[
				'https://en.wikipedia.org/w/index.php?title=Bill_Gates&action=edit',
				'https://en.wikipedia.org/w/index.php?title=Bill_Gates&action=edit'
			],
			[ 'ftp://google.com/#param=1', 'ftp://google.com/#param=1' ],
			[ 'https://zh.wikipedia.org/wiki/猫', 'https://zh.wikipedia.org/wiki/%E7%8C%AB' ],
			[
				'http://www.discogs.com/Various-Kad-Jeknu-Dragačevske-Trube-2',
				'http://www.discogs.com/Various-Kad-Jeknu-Draga%C4%8Devske-Trube-2'
			],
		];
		// @codingStandardsIgnoreEnd
		if ( function_exists( 'idn_to_ascii' ) ) {
			$tests[] = [ 'http://кц.рф/ru/', 'http://xn--j1ay.xn--p1ai/ru/' ];
		}

		return $tests;
	}

	/**
	 * Test the URL parsing function
	 *
	 * @param string $url URL
	 * @param expect $expect Expected parsed URL
	 * @dataProvider provideParseURL
	 */
	public function testParseURL( $url, $expect ) {
		$obj = new CheckIfDead();
		$this->assertEquals( $expect, $obj->parseURL( $url ) );
	}

	public function provideParseURL() {
		return [
			[
				'http://кц.рф/ru/', [
				'scheme' => 'http',
				'host'   => 'кц.рф',
				'path'   => '/ru/',
			]
			],
			[
				'http://www.discogs.com/Various-Kad-Jeknu-Dragačevske-Trube-2', [
				'scheme' => 'http',
				'host'   => 'www.discogs.com',
				'path'   => '/Various-Kad-Jeknu-Dragačevske-Trube-2',
			]
			],
		];
	}
}
