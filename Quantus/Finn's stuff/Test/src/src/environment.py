import math, time

from direct.task.Task import Task
from pandac.PandaModules import Shader, Fog, Vec2, LightAttrib, Spotlight, PerspectiveLens, VBase4
from pandac.PandaModules import Vec4, Point3, PointLight, Vec3, AmbientLight, DirectionalLight
from direct.interval.IntervalGlobal import Sequence, LerpFunc, LerpColorInterval

from src.rain import rainClass

# 1 day takes DAYNIGHTCYCLETIME seconds
DAYNIGHTCYCLETIME = 120.0
# some special function to set day/night cycle time
# smaller values makes night short, large value makes days short
# must not be 1.0
DAYNIGHTCYCLEFUNC = 0.01
# set the rain/norain cycle time
RAINCYCLEFUNC = 100.0
# default fog density during day
DEFAULTFOG = 0.0075
NIGHTFOGMULTIPLIER = 10.0
linFogMinRange = 50
linFogVarianceRange = 450

USERAIN = True
USELIGHT = True
USESKY = False
USEFOG = True
USESOUND = True

class environmentClass:
	def __init__( self, cameraPos, heightfield ):
		self.cameraPos = cameraPos
		self.heightfield = heightfield
		
		if USELIGHT:
			self.setupLight()
		if USESKY:
			self.setupSky()
		if USEFOG:
			self.setupFog()
		if USESOUND:
			self.setupSound()
		if USERAIN:
			self.setupRain()
		
		#taskMgr.doMethodLater(DAYNIGHTCYCLETIME/60.0, self.dayNightCycle, 'UpdateDayNight')
		taskMgr.doMethodLater(0.1, self.dayNightCycle, 'UpdateDayNight')
		taskMgr.doMethodLater(0.1, self.updateScene, 'updateScene' )
	
	def setupSound( self ):
		self.mySound1 = loader.loadSfx("data/sounds/rainshower.wav")
		self.mySound1.setLoop(True)
		self.mySound1.play()
	
	def setupSky( self ):
		self.skyNP = loader.loadModel( 'data/models/sky.bam.pz' )
		self.skyNP.reparentTo( render )
		self.skyNP.setScale( 4000, 4000, 1000 )
		self.skyNP.setPos( 0, 0, 0 )
		self.skyNP.setTexture( loader.loadTexture( 'data/textures/clouds.png' ) )
		self.skyNP.setShader( loader.loadShader( 'data/sky.sha' ) )
		
		'''self.skyFogNP = loader.loadModel( 'data/models/sphere.egg' )
		self.skyFogNP.reparentTo( base.camera )
		self.skyFogNP.setTwoSided( True )
		self.skyFogNP.setScale( 10 )
		self.skyFogNP.setPos( Vec3(0,0,4) )
		self.skyFogNP.setTransparent( True )'''
		
		sky		= Vec4( 0.25, 0.5, 1.0, 0.0 )					 # r, g, b, skip
		sky2 = Vec4( 1.0, 1.0, 1.0, 0.0 ) 
		clouds = Vec4( 0.004, 0.002, 0.008, 0.010 )		# vx, vy, vx, vy
		self.skyNP.setShaderInput( 'sky', sky )
		self.skyNP.setShaderInput( 'sky2', sky2 ) 
		self.skyNP.setShaderInput( 'clouds', clouds )
		render.setShaderInput( 'time', 0 )
	
	def setupLight( self ):
		#Default lightAttrib with no lights
		#self.lightAttrib = LightAttrib.makeAllOff() 
		
		# First we create an ambient light. All objects are affected by ambient
		# light equally
		#Create and name the ambient light
		
		self.ambientLight = AmbientLight( "ambientLight" )
		#Set the color of the ambient light
		self.ambientLight.setColor( Vec4( .1, .1, .1, 1 ) )
		self.alnp = render.attachNewNode(self.ambientLight)
		render.setLight(self.alnp)
		
		self.heightfield.mHeightFieldNode.setLightOff()
		
		self.ambientLight2 = AmbientLight( "ambientLight2" )
		#Set the color of the ambient light
		self.ambientLight2.setColor( Vec4( .1, .1, .1, 1 ) )
		self.al2np = render.attachNewNode(self.ambientLight2)
		self.heightfield.mHeightFieldNode.setLight(self.al2np)
		
		self.dlight = DirectionalLight('dlight')
		self.dlight.setColor(VBase4(1.0, 1.0, 0.6, 1))
		self.dlnp = render.attachNewNode(self.dlight.upcastToPandaNode())
		self.dlnp.setHpr(-90, -30, 0)
		self.heightfield.mHeightFieldNode.setLight(self.dlnp)
		
		# Now we create a spotlight. Spotlights light objects in a given cone
		# They are good for simulating things like flashlights
		self.spotlight = Spotlight( "spotlight" )
		self.spotlight.setColor( Vec4( .9, .9, .9, 1 ) )
		#The cone of a spotlight is controlled by it's lens. This creates the lens
		self.spotlight.setLens( PerspectiveLens() )
		#This sets the Field of View (fov) of the lens, in degrees for width and
		#height. The lower the numbers, the tighter the spotlight.
		self.spotlight.getLens().setFov( 30, 30 )
		# Attenuation controls how the light fades with distance. The numbers are
		# The three values represent the three constants (constant, linear, and
		# quadratic) in the internal lighting equation. The higher the numbers the
		# shorter the light goes.
		self.spotlight.setAttenuation( Vec3( 0.0, 0.0075, 0.0 ) ) 
		# This exponent value sets how soft the edge of the spotlight is. 0 means a
		# hard edge. 128 means a very soft edge.
		self.spotlight.setExponent( 60.0 )
		# Unlike our previous lights, the spotlight needs a position in the world
		# We are attaching it to the camera so that it will appear is if we are
		# holding a flashlight, but it can be attached to any NodePath
		#
		# When attaching a spotlight to a NodePath, you must use the
		# upcastToLensNode function or Panda will crash
		#camera.attachNewNode( self.spotlight.upcastToLensNode() ) 
		self.spnp = camera.attachNewNode( self.spotlight.upcastToLensNode() )
		render.setLight(self.spnp)
		self.heightfield.mHeightFieldNode.setLight(self.spnp)
		#self.lightAttrib = self.lightAttrib.addLight( self.spotlight )
		
		#Finally we set the light attrib to a node. In this case we are using render
		#so that the lights will effect everything, but you could put it on any
		#part of the scene
		#render.node().setAttrib( self.lightAttrib )
		
		# Create and start interval to spin the lights, and a variable to
		# manage them.
		#self.pointLightsSpin = self.pointLightHelper.hprInterval(6, Vec3(360, 0, 0))
		#self.pointLightsSpin.loop()
	
	def setupFog( self ):
		'''defaultExpFogColor = (0.33, 0.5, 1.0)
		self.expFog = Fog("exponentialFog")
		self.expFog.setColor(*defaultExpFogColor)
		self.expFog.setExpDensity(DEFAULTFOG)
		render.setFog(self.expFog)'''
		
		defaultLinFogColor = (0.33, 0.5, 1.0)
		self.linFog = Fog("linearFog")
		self.linFog.setColor(*defaultLinFogColor)
		self.linFog.setLinearRange(0, linFogMinRange + linFogVarianceRange)
		self.linFog.setLinearFallback(30, 60, 240)
		base.camera.attachNewNode(self.linFog)
		render.setFog(self.linFog)
		
		base.setBackgroundColor( defaultLinFogColor )
	
	def setupRain( self ):
		base.enableParticles()
		self.rain = rainClass()
		self.rain.reparentTo( base.camera )
		#self.rain.setPos( 0, 0, 5 )
		self.rain.setScale( 200 )
		#self.rain.particle.setPoolSize( 8192 )
		#self.rain.particle.setBirthRate( 2.000 )
		#self.rain.particle.renderer.setHeadColor(Vec4(1.00, 1.00, 1.00, 0.8))
		#self.rain.particle.renderer.setTailColor(Vec4(1.00, 1.00, 1.00, 0.2))
		self.rain.start( render )
	
	def dayNightCycle( self, task ):
		
		#print "dayNight", rainStrenght
		if USERAIN:
			rainStrenght = (RAINCYCLEFUNC**((math.sin(time.time()/(DAYNIGHTCYCLETIME/24.))+1.0)/2.0)-1.0)/(RAINCYCLEFUNC-1.0)
			self.rain.particle.setBirthRate( max( rainStrenght, 0.01 ) )
		
		sunPos = time.time()/(DAYNIGHTCYCLETIME/(math.pi*2))%(math.pi*2)
		dayNight = (math.sin(sunPos)+1.0)/2.0
		dayNight = (DAYNIGHTCYCLEFUNC**dayNight-1.0)/(DAYNIGHTCYCLEFUNC-1.0)
		
		if USELIGHT:
			#print dayNight
			c = (dayNight)/1.2 + 0.01
			#print dayNight, c	[commented by Finn]
			aLightCol = Vec4( c, c, c, 1 )
			self.ambientLight.setColor( aLightCol )
			
			
			if abs(sunPos/math.pi-1) < 0.5:
				self.dlnp.setHpr(-90, 180 + (dayNight-0.3) * 108, 0)
			else:
				self.dlnp.setHpr(-90, 0 - (dayNight-0.3) * 108, 0)
		
		# Time for clouds shader
		if USESKY:
			render.setShaderInput( 'time', task.time/4.0 )
			#dayNight = 1.0
			# color for clouds & fog
			#dayNight = ( math.sin(time.time()/DAYNIGHTCYCLETIME) + 1.0 ) / 2.0	# 0.0 for night, 1.0 for day
			sky		= Vec4( dayNight/4.0, dayNight/2.0, dayNight, 0.0 )					# r, g, b, skip
			sky2	 = Vec4( dayNight, dayNight, dayNight, 0.0 )
			# set colors
			self.skyNP.setShaderInput( 'sky', sky )
			self.skyNP.setShaderInput( 'sky2', sky2 )
		
		if USEFOG:
			#expFogColor = dayNight/3.0,dayNight/2.0,dayNight
			#self.expFog.setColor( *expFogColor )
			#self.expFog.setExpDensity(DEFAULTFOG*(NIGHTFOGMULTIPLIER-dayNight*(NIGHTFOGMULTIPLIER-1.0)))
			linFogColor = dayNight/3.0,dayNight/2.0,dayNight
			self.linFog.setColor( *linFogColor )
			fogRange = linFogMinRange + linFogVarianceRange*dayNight
			self.linFog.setLinearRange( fogRange/4., fogRange )
			self.linFog.setLinearFallback(fogRange/8., fogRange/4., fogRange)
			base.setBackgroundColor( linFogColor )
		return Task.again
	
	def updateScene( self, task ):
		# set position of the particle system
		if USERAIN:
			self.rain.setPos( base.camera.getPos() + Vec3( 0,0,200) )
		return Task.cont