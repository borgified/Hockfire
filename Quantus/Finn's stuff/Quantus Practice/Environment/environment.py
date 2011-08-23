import math, time
import heightfield
from direct.task.Task import Task
from pandac.PandaModules import Shader, Fog, Vec2, Spotlight, PerspectiveLens
from pandac.PandaModules import Vec4, Vec3, Point3, PointLight, AmbientLight, DirectionalLight
from pandac.PandaModules import VBase4
from direct.interval.IntervalGlobal import Sequence, LerpFunc, LerpColorInterval



# 1 day takes DAYNIGHTCYCLETIME seconds
DAYNIGHTCYCLETIME = 10.0
# some special function to set day/night cycle time
# smaller values makes night short, large value makes days short
# must not be 1.0
DAYNIGHTCYCLEFUNC = 0.001
# set the rain/norain cycle time
RAINCYCLEFUNC = 100.0
# default fog density during day

DEFAULTFOG = 0.0075
NIGHTFOGMULTIPLIER = 10.0
linFogMinRange = 50
linFogVarianceRange = 450

USELIGHT = True
USESKY = False
USEFOG = True
USESOUND = False
USERAIN = False
USENIGHT = False

class Environment:
	def __init__( self):
		self.cameraPos = base.camera.getPos()
		
		# create a heightfield
		self.heightfield = heightfield.Heightfield( base.camera )
		
		if USELIGHT:
			self.setupLight()
		if USESKY:
			self.setupSky()
		if USEFOG:
			self.setupFog()
		if USESOUND:
			self.setupSound()
		if USERAIN:
			from src.rain import rainClass
			self.setupRain()
		if USENIGHT:
			self.setupNight()
	
	def setupLight( self ):
		self.ambientLight = AmbientLight( 'ambientLight' )
		self.ambientLight.setColor( Vec4( 0.1, 0.1, 0.1, 1 ) )
		self.ambientLightNP = render.attachNewNode( self.ambientLight.upcastToPandaNode() )
		render.setLight(self.ambientLightNP)
		
		self.dlight = DirectionalLight('dlight')
		self.dlight.setColor(VBase4(0.8, 0.8, 0.5, 1))
		self.dlnp = render.attachNewNode(self.dlight.upcastToPandaNode())
		self.dlnp.setHpr(0, -30, 0)
		render.setLight(self.dlnp)
		'''
		# First we create an ambient light. All objects are affected by ambient
		# light equally
		#Create and name the ambient light
		self.ambientLight = AmbientLight( "ambientLight" )
		#Set the color of the ambient light
		self.ambientLight.setColor( VBase4( 0.1, 0.1, 0.1, 1 ) )
		#Make the light affect render (ie everything)
		render.setLight(render.attachNewNode(self.ambientLight.upcastToPandaNode()))
		'''
	
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
		
		sky		= Vec4( 0.25 , 0.5  , 1.0  , 0.0   ) # r, g, b, skip
		sky2 	= Vec4( 1.0  , 1.0  , 1.0  , 0.0   ) 
		clouds 	= Vec4( 0.004, 0.002, 0.008, 0.010 ) # vx, vy, vx, vy
		self.skyNP.setShaderInput( 'sky'   , sky    )
		self.skyNP.setShaderInput( 'sky2'  , sky2   ) 
		self.skyNP.setShaderInput( 'clouds', clouds )
		render.setShaderInput( 'time', 0 )
	
	def setupFog( self ):
		'''defaultExpFogColor = (0.33, 0.5, 1.0)
		self.expFog = Fog("exponentialFog")
		self.expFog.setColor(*defaultExpFogColor)
		self.expFog.setExpDensity(DEFAULTFOG)
		render.setFog(self.expFog)'''
		
		defaultLinFogColor = (0.165, 0.25, 0.5)
		self.linFog = Fog("linearFog")
		self.linFog.setColor(*defaultLinFogColor)
		self.linFog.setLinearRange(0, linFogMinRange + linFogVarianceRange)
		self.linFog.setLinearFallback(30, 60, 240)
		base.camera.attachNewNode(self.linFog)
		render.setFog(self.linFog)
		
		base.setBackgroundColor( defaultLinFogColor )
	
	def setupSound( self ):
		self.mySound1 = loader.loadSfx("data/sounds/rainshower.wav")
		self.mySound1.setLoop(True)
		self.mySound1.play()
	
	def setupRain( self ):
		base.enableParticles()
		self.rain = rainClass()
		self.rain.reparentTo( base.camera )
		self.rain.setScale( 200 )
		self.rain.start( render )
	
	def setupNight( self ):
		taskMgr.doMethodLater(0.05, self.dayNightCycle, 'UpdateDayNight')
		taskMgr.doMethodLater(0.05, self.updateScene, 'updateScene' )
	
	def dayNightCycle( self, task ):
		
		#print "dayNight", rainStrenght
		if USERAIN:
			rainStrenght = (RAINCYCLEFUNC**((math.sin(time.time()/(DAYNIGHTCYCLETIME/24.))+1.0)/2.0)-1.0)/(RAINCYCLEFUNC-1.0)
			self.rain.particle.setBirthRate( max( rainStrenght, 0.01 ) )
		
		sunPos = time.time()/(DAYNIGHTCYCLETIME/(math.pi*2))%(math.pi*2)
		dayNight = (math.sin(sunPos)+1.0)/2.0
		#dayNight = (DAYNIGHTCYCLEFUNC**dayNight-1.0)/(DAYNIGHTCYCLEFUNC-1.0)
		
		if USELIGHT:
			#print dayNight
			c = (dayNight)/1.5 + 0.1
			#print dayNight, c	[commented by Finn]
			aLightCol = Vec4( c, c, c, 1 )
			#self.ambientLight.setColor( aLightCol )
			
			self.dlnp.setHpr(0, (sunPos/(2*math.pi)-0.5) * 360, 0)
		
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