import direct.directbase.DirectStart
from pandac.PandaModules import *

from direct.task import Task
from direct.actor import Actor
from direct.interval.IntervalGlobal import *
import math
from camera.godCamera import GodCamera
#Load the first environment model
environ = loader.loadModel("models/environment")
environ.reparentTo(render)
environ.setScale(0.25,0.25,0.25)
environ.setPos(-8,42,0)

tank = loader.loadModel("IC Stealth Tank 4.3.x")
tank.reparentTo(render)
tank.setPos(0,0,1)

base.disableMouse()
godCam = GodCamera()
godCam.enable()

run()
