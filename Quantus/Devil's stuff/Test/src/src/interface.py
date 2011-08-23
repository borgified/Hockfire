from pandac.PandaModules import Texture, TextureStage, TexGenAttrib, CompassEffect, NodePath

class interfaceClass:
  def __init__( self ):
    # --- create a compass ---
    # load a plane
    self.compassNp = loader.loadModelCopy( "data/models/plane.egg" )
    self.compassNp.setColor( 1,1,1,1 )
    self.compassNp.setLightOff()
    self.compassNp.setScale( 0.25 )
    self.compassNp.setHpr( 0, 270,0 )
    
    # assign texture
    self.tex0 = loader.loadTexture( 'data/textures/kompass.png' )
    self.compassNp.setTexture( self.tex0, 0 )
    self.compassNp.setTransparency(1)
    
    # create position node for the compass
    self.compassPositionNp = NodePath( "CompassPositionNp" )
    self.compassPositionNp.reparentTo( base.camera )
    self.compassPositionNp.setPos( 0.0, 0.8, -0.5 )
    self.compassNp.reparentTo( self.compassPositionNp )
    
    # assign CompassEffect
    Effect=CompassEffect.make(render)
    self.compassNp.node().setEffect(Effect)
    # --- compass done ---
    
