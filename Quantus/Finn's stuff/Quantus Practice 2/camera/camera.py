from direct.showbase.DirectObject import *
from pandac.PandaModules import Vec3, Vec4, Point3, VBase2
from direct.task import Task
import math
from direct.gui.OnscreenText import OnscreenText
from pandac.PandaModules import TextNode, NodePath

class Camera(DirectObject):
	def __init__(self, heightfield):
		self.heightfield = heightfield
		base.camLens.setFar(25000)
		base.camera.setPos(Vec3(0,6000,3000))
		self.camControlNp = NodePath("camControlNp")
		base.camera.reparentTo(self.camControlNp)
		self.camControlNp.reparentTo(render)
		base.camera.lookAt(self.camControlNp)
		self.guiposOnscreenText = OnscreenText(text = 'position', fg=(1,1,1,1), pos = (-0.9, 0.9), scale = 0.07, mayChange=True, align=TextNode.ALeft )
		taskMgr.doMethodLater(0.1, self.updateGuiPosTask, 'updateGuiPosTask')
		taskMgr.add(self.setHeight, 'updateGuiPosTask')
		self.accept( "w", self.forward)
		self.accept( "a", self.left)
		self.accept( "s", self.back)
		self.accept( "d", self.right)
		self.accept( "q", self.clockwise)
		self.accept( "e", self.anticlockwise)
		self.accept( "r", self.zoomIn)
		self.accept( "f", self.zoomOut)
		
		self.accept( "w-up", self.forwardOff)
		self.accept( "a-up", self.leftOff)
		self.accept( "s-up", self.backOff)
		self.accept( "d-up", self.rightOff)
		self.accept( "q-up", self.clockwiseOff)
		self.accept( "e-up", self.anticlockwiseOff)
		self.accept( "r-up", self.zoomInOff)
		self.accept( "f-up", self.zoomOutOff)
		
	def forward( self ):
		taskMgr.add(self.updateCamPosTask, 'camForward', 
				extraArgs=[Vec3(0, -1, 0),Vec3(0, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def back( self ):
		taskMgr.add(self.updateCamPosTask, 'camBack', 
				extraArgs=[Vec3(0, 1, 0),Vec3(0, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def left( self ):
		taskMgr.add(self.updateCamPosTask, 'camLeft', 
				extraArgs=[Vec3(1, 0, 0),Vec3(0, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def right( self ):
		taskMgr.add(self.updateCamPosTask, 'camRight', 
				extraArgs=[Vec3(-1, 0, 0),Vec3(0, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def clockwise( self ):
		taskMgr.add(self.updateCamPosTask, 'camClockwise', 
				extraArgs=[Vec3(0, 0, 0), Vec3(-1, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def anticlockwise( self ):
		taskMgr.add(self.updateCamPosTask, 'camAnticlockwise', 
				extraArgs=[Vec3(0, 0, 0), Vec3(1, 0, 0),Vec3(0, 0, 0)], appendTask=True)
	def zoomIn( self ):
		taskMgr.add(self.updateCamPosTask, 'camZoomIn', 
				extraArgs=[Vec3(0, 0, 0), Vec3(0, 0, 0),Vec3(0, 1, 0)], appendTask=True)
	def zoomOut( self ):
		taskMgr.add(self.updateCamPosTask, 'camZoomOut', 
				extraArgs=[Vec3(0, 0, 0), Vec3(0, 0, 0),Vec3(0, -1, 0)], appendTask=True)
	
	def forwardOff( self ):
		taskMgr.remove('camForward')
	def backOff( self ):
		taskMgr.remove('camBack')
	def leftOff( self ):
		taskMgr.remove('camLeft')
	def rightOff( self ):
		taskMgr.remove('camRight')
	def clockwiseOff( self ):
		taskMgr.remove('camClockwise')
	def anticlockwiseOff( self ):
		taskMgr.remove('camAnticlockwise')
	def zoomInOff( self ):
		taskMgr.remove('camZoomIn')
	def zoomOutOff( self ):
		taskMgr.remove('camZoomOut')

		
	def updateCamPosTask( self, pos, hpr, zoom, task):
		timeMulti = 45.0 * globalClock.getDt()
		timeMultiPos = Vec3(timeMulti * pos.getX(),timeMulti * pos.getY(),timeMulti * pos.getZ())
		timeMultiHpr = Vec3(timeMulti * hpr.getX(),timeMulti * hpr.getY(),timeMulti * hpr.getZ())
		timeMultiZoom = Vec3(timeMulti * zoom.getX(),timeMulti * zoom.getY(),timeMulti * zoom.getZ())
		self.camControlNp.setPos(self.camControlNp, timeMultiPos * 20)
		self.camControlNp.setHpr(self.camControlNp, timeMultiHpr)
		base.camera.setPos(base.camera, timeMultiZoom * 20)
		return task.cont
	
	def setHeight( self , task):
		pos = [ self.camControlNp.getX(), self.camControlNp.getY() ]
		elevation = self.heightfield.get_elevation( pos )
		self.camControlNp.setZ( render, elevation)
		return task.cont
	
	def updateGuiPosTask( self, task ):
		text = "%s:%s" % (str(base.camera.getPos( render )), str(base.camera.getHpr( render )))
		self.guiposOnscreenText.setText( text )
		return Task.again