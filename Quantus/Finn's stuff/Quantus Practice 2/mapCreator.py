from direct.showbase.DirectObject import *
from pandac.PandaModules import loadPrcFileData, DirectionalLight, VBase4

from camera import camera
from environment import mapBuilder, sectorBuilder


'''
Finn: LoadPrcFileData allows settings which are normally set in 
a panda config file to be set for this program specifically at runtime
'''
#Finn: sync-video makes the framerate limited to the screen refresh rate
loadPrcFileData("", "sync-video 1" ) 

#the color depth should be as large as posible (hopefully 24 bits+)
loadPrcFileData("", "color-bits 1" ) 

#the depth buffer should be as large as possible too (hopefully 24 bits+ too)
loadPrcFileData("", "depth-bits 1" ) 
#loadPrcFileData("", "support-threads #f" )
#Finn: show-frame-rate-meter shows the framerate in the top right 
#(this one isn't documented so worth noting) 
loadPrcFileData("", "show-frame-rate-meter 1")

# -- set Fullscreen --
#loadPrcFileData("",  "fullscreen 1")

# -- set window title --
loadPrcFileData("", "window-title Quantus : Map Creator")

#This has to be imported after the loadPrcFileData calls or they have no effect
import direct.directbase.DirectStart

class MapCreator( DirectObject ):
	def __init__( self ):
		#render.setShaderAuto()
		base.disableMouse()
		
		self.dlight = DirectionalLight('dlight')
		self.dlight.setColor(VBase4(0.8, 0.8, 0.5, 1))
		self.dlnp = render.attachNewNode(self.dlight.upcastToPandaNode())
		self.dlnp.setHpr(0, -30, 0)
		
		self.mapBuilder = mapBuilder.MapBuilder()
		self.sectorBuilder = sectorBuilder.SectorBuilder()
		
		render.setLight(self.dlnp)
		self.camera = camera.Camera(self.sectorBuilder.heightfield)
		
		self.step()
	
	def step( self ):
		# render next frame
		taskMgr.step()

if __name__ == '__main__':
	mapCreator = MapCreator()
	while True:
		mapCreator.step()
