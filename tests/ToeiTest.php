<?php
require(__DIR__."/../autoload.php");

use Toei\Toei;
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class ToeiTest extends TestCase{
	use TestCaseTrait;

	static private $PDO = null;
	private $conn = null;
	private $Toei = null;

	public function getConnection(){
		if( $this->conn === null ){
			if( self::$PDO === null ) self::$PDO = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
			$this->conn = $this->createDefaultDBConnection(self::$PDO, DB_NAME);
		}
		return $this->conn;
	}

	public function getDataSet(){
		return $this->createMySQLXMLDataSet(__DIR__."/fixtures/toei.xml");
	}

	public function setUp(){
		parent::setUp();
		$config = json_decode(file_get_contents(__DIR__."/configs/actions.json"));
		$this->Toei = new Toei(self::$PDO, $config);
	}

	/**
	 * @dataProvider projectProvider
	 */
	public function testProject($id, $exec, $expected){
		$this->Toei->setId($id);
		$result = $this->Toei->project($exec);
		$this->assertSame($expected, $result);
	}

	public function projectProvider(){
		$expecteds = [];
		$expecteds[0] = preg_replace("/\s+/", " ", <<< 'EOD'
SELECT a.*
FROM (
	(
		SELECT 'register' as action, id as id, created as time
		FROM users
		WHERE id = 2 AND created IS NOT NULL
	) union all (
		SELECT 'withdraw' as action, id as id, deleted as time
		FROM users
		WHERE id = 2 AND deleted IS NOT NULL AND (created > '2000-01-01 00:00:00')
	) union all (
		SELECT 'send_message' as action, sender as id, created as time
		FROM messages
		WHERE sender = 2 AND created IS NOT NULL
	) union all (
		SELECT 'recieve_message' as action, receiver as id, created as time
		FROM messages
		WHERE receiver = 2 AND created IS NOT NULL
	)
) AS a
ORDER BY a.time ASC
EOD
		);
		$expecteds[1] = [
			[
				"action" => "register",
				"id" => "2",
				"time" => "2017-01-21 09:57:48"
			],
			[
				"action" => "recieve_message",
				"id" => "2",
				"time" => "2017-01-21 12:01:44"
			],
			[
				"action" => "send_message",
				"id" => "2",
				"time" => "2017-02-04 21:54:17"
			],
			[
				"action" => "send_message",
				"id" => "2",
				"time" => "2017-03-20 17:54:46"
			],
			[
				"action" => "send_message",
				"id" => "2",
				"time" => "2017-03-20 17:56:23"
			],
			[
				"action" => "withdraw",
				"id" => "2",
				"time" => "2017-03-20 18:03:30"
			]
		];
		return [
			[2, false, $expecteds[0]],
			[2, true, $expecteds[1]]
		];
	}
}
