"""
ShadowCaster, by Reto Spoerri

Thanks to ynjh_jo for some improvement hints

This is a modified version from the sombras.py by Edwood Grant
(A Rez Clone Project), a little bit simplified and more automatic.

Create a cheesy shadow effect by rendering the view of an
object (e.g. the local avatar) from a special camera as seen from
above (as if from the sun), using a solid gray foreground and a
solid white background, and then multitexturing that view onto the
world.

This is meant primarily as a demonstration of multipass and
multitexture rendering techniques.  It's not a particularly great
way to do shadows.

But it does work mostly :)

Known problems:
- the shadowtexture is projected onto the shadow receiving object from the
lightsource, so if a shadow receiving object is in front
(viewed from the lightsource) of the shadow making object
it will receive the shadows anyway.

- check the number of texture stages your graphics card supports if you
dont see shadows.
(you'll see the NUMBER OF TEXTURE STAGES when you run the programm)
"""

from pandac.PandaModules import Point3, Vec3, VBase4, Texture, Camera, NodePath, OrthographicLens, TextureStage, Mat4

SHADOWCOLOR = [0.5, 0.5, 0.5, 1, 1]

# enable to see the render outputs of the shadow cameras
#base.bufferViewer.toggleEnable()

# global
shadowCasterObjectCounter = 0
class ShadowCasterClass:
    texXSize = 2048
    texYSize = 2048
    groundPath = None
    objectPath = None
    
    def __init__(self, objectPath, groundPath, lightPos=Vec3(0,0,1)):
        """ ShadowCaster::__init__
        objectPath is the shadow casting object
        groundPath is the shadow receiving object
        lightPos is the lights relative position to the objectPath
        """
        
        # uniq id-number for each shadow
        global shadowCasterObjectCounter
        shadowCasterObjectCounter += 1
        self.objectShadowId = shadowCasterObjectCounter
        
        # the object which will cast shadows
        self.objectPath = objectPath
        
        # get the objects bounds center and radius to
        # define the shadowrendering camera position and filmsize
        try:
            objectBoundRadius = self.objectPath.getBounds().getRadius()
            objectBoundCenter = self.objectPath.getBounds().getCenter()
        except:
            print "failed"
            objectBoundCenter = Point3( 0,0,0 )
            objectBoundRadius = 1
        
        lightPath = objectPath.attachNewNode('lightPath%i'%self.objectShadowId)
        # We can change this position at will to change the angle of the sun.
        lightPath.setPos( objectPath.getParent(), objectBoundCenter )
        self.lightPath = lightPath
        
        # the film size is the diameter of the object
        self.filmSize = objectBoundRadius * 2
        
        # Create an offscreen buffer to render the view of the avatar
        # into a texture.
        self.buffer = base.win.makeTextureBuffer(
            'shadowBuffer%i'%self.objectShadowId, self.texXSize, self.texYSize)

        # The background of this buffer--and the border of the
        # texture--is pure white.
        clearColor = VBase4(1, 1, 1, 1)
        self.buffer.setClearColor(clearColor)
        
        self.tex = self.buffer.getTexture()
        self.tex.setBorderColor(clearColor)
        self.tex.setWrapU(Texture.WMBorderColor)
        self.tex.setWrapV(Texture.WMBorderColor)

        # Set up a display region on this buffer, and create a camera.
        dr = self.buffer.makeDisplayRegion()
        self.camera = Camera('shadowCamera%i'%self.objectShadowId)
        self.cameraPath = self.lightPath.attachNewNode(self.camera)
        self.camera.setScene(self.objectPath)
        dr.setCamera(self.cameraPath)
        
        self.setLightPos( lightPos )
        
        # Use a temporary NodePath to define the initial state for the
        # camera.  The initial state will render everything in a
        # flat-shaded gray, as if it were a shadow.
        initial = NodePath('initial%i'%self.objectShadowId)
        initial.setColor( *SHADOWCOLOR )
        initial.setTextureOff(2)
        self.camera.setInitialState(initial.getState())
        
        # Use an orthographic lens for this camera instead of the
        # usual perspective lens.  An orthographic lens is better to
        # simulate sunlight, which is (almost) orthographic.  We set
        # the film size large enough to render a typical avatar (but
        # not so large that we lose detail in the texture).
        self.lens = OrthographicLens()
        self.lens.setFilmSize(self.filmSize, self.filmSize)
        self.camera.setLens(self.lens)
        
        # Finally, we'll need a unique TextureStage to apply this
        # shadow texture to the world.
        self.stage = TextureStage('shadow%i'%self.objectShadowId)

        # Make sure the shadowing object doesn't get its own shadow
        # applied to it.
        self.objectPath.setTextureOff(self.stage)
        
        # the object which will receive shadows
        self.setGround( groundPath )
    
    def setLightPos( self, lightPos ):
        """ sets the position of the light
        """
        self.cameraPath.setPos( lightPos )
        self.cameraPath.lookAt( self.lightPath, 0,0,0 )
    
    def setGround(self, groundPath):
        """ Specifies the part of the world that is to be considered
        the ground: this is the part onto which the rendered texture
        will be applied. """
        
        if self.groundPath:
            self.groundPath.clearProjectTexture(self.stage)
        
        self.groundPath = groundPath
        self.groundPath.projectTexture(self.stage, self.tex, self.cameraPath)

    def clear(self):
        """ Undoes the effect of the ShadowCaster. """
        if self.groundPath:
            self.groundPath.clearProjectTexture(self.stage)
            self.groundPath = None

        if self.lightPath:
            self.lightPath.detachNode()
            self.lightPath = None

        if self.cameraPath:
            self.cameraPath.detachNode()
            self.cameraPath = None
            self.camera = None
            self.lens = None

        if self.buffer:
            base.graphicsEngine.removeWindow(self.buffer)
            self.tex = None
            self.buffer = None

# global
lightAngle = 0

def objectShadowClass( objectPath, groundPath ):
    """ add a shadow to the objectPath, which is cast on the groundPath
    from the lightPos
    """
    # define the source position of the light, relative to the objectPath
    lightPos = Vec3( 0,0,100 )
    # add shadows to the object
    sc = ShadowCasterClass(objectPath, groundPath, lightPos)
    
    from direct.task import Task
    import math
    
    def lightRotate( task, sc = sc ):
        """ rotate the light around the object
        """
        global lightAngle
        lightAngle += math.pi / 180 * globalClock.getDt() * 10
        r = 50
        sc.setLightPos( Vec3( r * math.cos(lightAngle), r * math.sin(lightAngle), 100 ) )
        return Task.cont
    
    taskMgr.add(lightRotate, 'lightRotateTask')
    
    return sc

if __name__ == '__main__':
    import direct.directbase.DirectStart
    
    print 'MAXIMUM # OF TEXTURE STAGES (SHADOWS + TEXTURES) :',base.win.getGsg().getMaxTextureStages()
    print '''if the texture count on the objects you use,
is the same like the texture stages, you cant add shadows to them'''

    # change the model paths
    from pandac.PandaModules import getModelPath
    from pandac.PandaModules import getTexturePath
    from pandac.PandaModules import getSoundPath 
    modelPath = '/usr/local/panda:.'
    getModelPath( ).appendPath( modelPath )
    getTexturePath( ).appendPath( modelPath )
    getSoundPath( ).appendPath( modelPath ) 
    
    camera.setPos(55,-30,25)
    camera.lookAt(render)
    camera.setP(camera,3)
    cameraMat=Mat4(camera.getMat())
    cameraMat.invertInPlace()
    base.mouseInterfaceNode.setMat(cameraMat)
    
    # load the models
    # shadow casting object
    modelNp = loader.loadModelCopy( 'models/panda.egg' )
    modelNp.reparentTo( render )
    # shadow receiving object
    groundNp = loader.loadModelCopy( 'models/environment.egg' )
    groundNp.reparentTo( render )
    # add the shadow to the shadow receiving object
    objectShadowClass( modelNp, groundNp )
    
    run()