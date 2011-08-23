from direct.showbase.DirectObject import *
from pandac.PandaModules import loadPrcFileData, Vec3, Vec4

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

tank = loader.loadModel (.\Models\IC Stealth Tank 4.3.3ds)

class Quantus( DirectObject ):
	def __init__( self ):
		#render.setShaderAuto()
		#base.disableMouse()
		
		self.camera = camera.Camera()
		
		self.environment = environment.Environment()
		
		self.perPixelEnabled = False
		self.accept( "l", self.togglePerPixelLighting )
		
		self.step()
		
	def togglePerPixelLighting( self ):
		if self.perPixelEnabled:
			self.perPixelEnabled = False
			render.clearShader()
		else:
			self.perPixelEnabled = True
			render.setShaderAuto()
	
	def step( self ):
		# set camera at ground
		pos = [ base.camera.getX(), base.camera.getY() ]
		elevation = self.environment.heightfield.get_elevation( pos )
		base.camera.setZ( render, elevation + 5.0 )
		
		# render next frame
		taskMgr.step()

if __name__ == '__main__':
	quantus = Quantus()
	while True:
		quantus.step()