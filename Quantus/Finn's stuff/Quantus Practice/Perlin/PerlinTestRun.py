import direct.directbase.DirectStart
import PerlinTest

try:
	import psyco
	psyco.full()
except ImportError:
	pass


pTest = PerlinTest.PerlinTest()
run()