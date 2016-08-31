<?PHP

error_reporting(E_ALL);

require '../src/Nivo/Debug/Chrome.php';

interface FooInterface
{
	public function foo();
}

interface BarInterface
{
	public function bar();
}

interface DummyInterface
{
	public function dummy();
}

/*
 * Lets create some example classes
 */
abstract class AbstractDummy
{
	/**
	 * DocBlock initialization lorem ipsum dolor
	 * @return void
	 */
	public function init(){}

	/**
	 * DocBlock initialization lorem ipsum dolor
	 * @return void
	 */
	abstract protected function abstractDummyMethod();
}

class Dummy extends AbstractDummy implements DummyInterface
{
	public function doStuff(array $dummy, array $collection, FooInterface $optional = null){}

	public function run(FooInterface $dummy){}

	public function tearDown(){}

	public function dummy(){}

	protected function abstractDummyMethod(){}

	private function helper($someString){}
}

class SpecialDummy extends Dummy implements FooInterface, BarInterface
{

	public $prop_public = "FoobarName";

	protected $prop_protected = "PROTECTED";

	private $prop_private = "PRIVATE";

	public function __construct(){
		$this->SOME_PROPERTY______ = 112345;
	}

	public function foobar(){}

	public function tearDown(){}

	private function helper($someString){}

	// - FooInterface Implementaion

	/**
	 * Lorem
	 */
	public function foo(){}

	// BarInterface Interface Implementaion

	public function bar(){}
}

/*
 * Now we can send a representation of the current instance
 * to the chrome extension. In order to see any results
 * the extension needs to be active.
 */
$dummy = new SpecialDummy();

\Nivo\Debug\Chrome::dump($dummy);

echo 'ok';