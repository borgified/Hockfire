from direct.showbase.DirectObject import *
from pandac.PandaModules import Vec3, Vec4
from direct.task import Task
from terrain import heightfield
from direct.stdpy import threading
import math

class SectorBuilder():
	def __init__(self):
		self.heightfield = heightfield.Heightfield()
		#self.heightfield.start()
		
		
		