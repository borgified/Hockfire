from direct.showbase.DirectObject import *
from pandac.PandaModules import loadPrcFileData, Vec3, Vec4, Filename

from Camera import camera
from Environment import environment


'''
Finn: LoadPrcFileData allows settings which are normally set in 
a panda config file to be set for this program specifically at runtime
'''
#Finn: sync-video makes the framerate limited to the screen refresh rate
loadPrcFileData("", "sync-video 1" ) 

#Finn: show-frame-rate-meter shows the framerate in the top right 
#(this one isn't documented so worth noting) 
loadPrcFileData("", "show-frame-rate-meter 1")

# -- set Fullscreen --
#loadPrcFileData("",  "fullscreen 1")

# -- set window title --
loadPrcFileData("", "window-title Quantus")

#This has to be imported after the loadPrcFileData calls or they have no effect
import direct.directbase.DirectStart

#this and the following commented stuff is my attempt to bring a tank into Quantus
class tank( DirectObject ):
	def __init__( self ):
		self.tankModel = loader.loadModel('data/tank.x')
		self.tankModel.reparentTo(render)
		self.accept( "m", self.move )
	
	def move(self):
		self.tankModel.setY(self.tankModel, 1)

class Quantus( DirectObject ):
	def __init__( self ):
		#render.setShaderAuto()
		#base.disableMouse()
		
		self.camera = camera.Camera()
		
		self.environment = environment.Environment()
		
		self.perPixelEnabled = False
		self.accept( "l", self.togglePerPixelLighting )
		
		self.vehicleModeEnabled = False
		self.accept( "f", self.toggleVehicleMode )
		
		self.tank = tank()
		
		pos = [ base.camera.getX(), base.camera.getY() ]
		elevation = self.environment.heightfield.get_elevation( pos )
		
		self.tank.tankModel.setZ( render, elevation + 2.0 )
		
		self.step()
		

	def togglePerPixelLighting( self ):
		if self.perPixelEnabled:
			self.perPixelEnabled = False
			render.clearShader()
		else:
			self.perPixelEnabled = True
			render.setShaderAuto()
	
	def toggleVehicleMode( self ):
		if self.vehicleModeEnabled:
			self.vehicleModeEnabled = False
			base.camera.reparentTo(self.camera.camMoveNode)
			base.camera.setZ(self.camera.camMoveNode, 2)
		else:
			self.vehicleModeEnabled = True
			base.camera.reparentTo(self.tank.tankModel)
			base.camera.setZ(self.tank.tankModel, 2)
			
	
	def step( self ):
		# set camera at ground
		
		pos = [ self.camera.camMoveNode.getX(), self.camera.camMoveNode.getY() ]
		elevation = self.environment.heightfield.get_elevation( pos )
		self.camera.camMoveNode.setZ( render, elevation + 3.0 )
		
		tankPos = [ self.tank.tankModel.getX(), self.tank.tankModel.getY() ]
		tankElevation = self.environment.heightfield.get_elevation( tankPos )
		if (tankElevation + 2.0 > self.tank.tankModel.getZ()):
			self.tank.tankModel.setZ( render, tankElevation + 2.0 )
		# render next frame
		taskMgr.step()



if __name__ == '__main__':
	quantus = Quantus()
	
	while True:
		quantus.step()
		
