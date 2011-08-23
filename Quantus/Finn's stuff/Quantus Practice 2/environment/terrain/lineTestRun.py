import direct.directbase.DirectStart
import lineTest

try:
	import psyco
	psyco.full()
except ImportError:
	pass


pTest = lineTest.PerlinTest()
run()
