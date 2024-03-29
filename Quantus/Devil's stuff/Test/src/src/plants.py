# thanks to ThomasEgi for a lot of performance hints

import random, math, time
from operator import add, mul

from direct.showbase.DirectObject import *
from pandac.PandaModules import Texture, TextureStage, Vec2, BitMask32, Vec3, Point3, NodePath
from pandac.PandaModules import PerlinNoise2, FadeLODNode #PerlinNoise, PerlinNoise3, 
from direct.task.Task import Task

# fade plant slowly in once they are created
FADEIN = True

# if chance is to low a plant may never be generated
# increasing the PLANTMAPSIZE will make sure the plant is placed sometimes

# functions to distribute the plants on the terrain
from plantDistribution import *

# ----- plantClass settings -----
# pitch (steilheit):
#   0.01 gives very steep increase
#   2.0 gives a low increase
# percent:
#   defines the position where the 0 point is
# inverse:
#   returns the inverse of the value
def distFuncCustom( noiseFuncResult, pitch, percent, inverse ):
  t0 = noiseFuncResult+((percent-50.0)*0.02)
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** pitch
  t2 = t1 * noiseFuncResultSign * inverse
  return t2

def distFuncAdd( noiseFuncResult, data1, data2, add ):
  func1, params1 = data1
  func2, params2 = data2
  res = func1( noiseFuncResult, *params1) + func2( noiseFuncResult, *params2) + add
  return res

def distFuncOne( noiseFuncResult ):
  return 1.0

scaleX = scaleY = 60
table_size = 256
seed = 1
noiseFunc60 = PerlinNoise2(scaleX, scaleY, table_size, seed)
#noiseFunc60.setScale( 60 )
scaleX = scaleY = 120
noiseFunc120 = PerlinNoise2(scaleX, scaleY, table_size, seed)
#noiseFunc120.setScale( 120 )
scaleX = scaleY = 240
noiseFunc240 = PerlinNoise2(scaleX, scaleY, table_size, seed)
#noiseFunc240.setScale( 240 )

def createSmallPlants( cameraPos, cameraRot, heightfield ):
    #print "--- small sized plants ---"  [commented by Finn]
    # number of tiles which are shown to each side of the player
    TILES = 4
    # the size of 1 tile
    PLANTMAPSIZE = 15
    #    name         : [ chance, model,                                                  minScale, scaleVarianz, [ noise, distribution, inverse ]
    plantmodelDict = {
      'grass'       : [  5.000, 'data/models/grass'                              ,  0.100,  0.100, [  noiseFunc60
                                                                                                   , distFuncOne  
                                                                                                   , [] ] ]
    , 'farn'        : [  1.250, 'data/models/3d-plant/fern_3ds/farn_1'           ,  3.000, 10.000, [  noiseFunc60
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 50, -1] ] ]
    , 'vsml-green'  : [  1.250, 'data/models/alice-nature--shrubbery/shrubbery'  ,  0.100,  0.100, [  noiseFunc60
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 50,  1] ] ]
    }
    return plantsClass( cameraPos, heightfield, plantmodelDict, TILES, PLANTMAPSIZE, False )
    
def createMediumPlants( cameraPos, cameraRot, heightfield ):
    #print "--- medium sized plants ---"  [commented by Finn]
    # number of tiles which are shown to each side of the player
    TILES = 4
    # the size of 1 tile
    PLANTMAPSIZE = 60
    #    name         : [ chance, model,                                                 minScale, scaleVarianz, [ noise, distribution ]
    plantmodelDict = { 
      'sml-green'   : [  0.250, 'data/models/bvw-f2004--plant1/plants1'          ,  0.400,  0.350, [  noiseFunc60
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 50,  1] ] ]
    , 'blueten-sml' : [  5.600, 'data/models/alice-nature--shrubbery2/shrubbery2',  0.010,  0.010, [  noiseFunc60
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 80, -1] ] ]
#    , 'blueten-med' : [  3.000, 'data/models/alice-nature--shrubbery2/shrubbery2',  0.010,  0.010, [ noiseFunc240
#                                                                                                   , distFuncCustom  
#                                                                                                   , [ 0.1, 25,  1] ] ]
    , 'med-green1'  : [  0.300, 'data/models/bvw-f2004--plant3/plants3'          ,  0.100,  0.100, [ noiseFunc240
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 40,  1] ] ] # inside forest only
    , 'pflanze'     : [  0.100, 'data/models/pflanze-v1/pflanze'                 ,  0.500,  0.500, [ noiseFunc240
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 50, -1] ] ] # not close to forest
#    , 'med-green3'  : [  0.300, 'data/models/bvw-f2004--plant3/plants3'          ,  0.050,  0.100, [ noiseFunc240
#                                                                                                   , distFuncCustom  
#                                                                                                   , [ 0.1, 60, -1] ] ] # outside of forest                     
    , 'own-palm-tny': [  0.100, 'data/models/3d-plant/palm/palm-v1'              ,  2.000,  3.000, [ noiseFunc240
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 75, -1] ] ] # tiny   palms ( 2.. 5)
    }
    return plantsClass( cameraPos, heightfield, plantmodelDict, TILES, PLANTMAPSIZE, False )

# plants with different lod's
plantLodModels = { 'wood-palm' : [ [ 9999, 140, 'data/models/3d-plant/palm/palm-lod1' ]
                                 , [  140,   0, 'data/models/3d-plant/palm/palm-v1'   ] ]
                 , 'green-palm': [ [ 9999, 140, 'data/models/bvw-f2004--plant3/plant-lod1-v3-2side-v2' ]
                                 , [  140,   0, 'data/models/bvw-f2004--plant3/plants3'   ] ]
                 , 'big-fern'  : [ [ 9999, 140, 'data/models/pflanze-v1/pflanze' ]
                                 , [  140,   0, 'data/models/pflanze-v1/pflanze' ] ] }

def createLargePlants( cameraPos, cameraRot, heightfield ):
    #print "--- large sized plants ---"  [commented by Finn]
    # number of tiles which are shown to each side of the player
    TILES = 3
    # the size of 1 tile
    PLANTMAPSIZE = 100
    #    name         : [ chance, model,                                                  minScale, scaleVarianz, [ noise, distribution ]
    # with lod
    plantmodelDict = { 
      'own-palm-sml': [  0.200, plantLodModels['wood-palm' ]                    ,  5.000,  5.000,  [ noiseFunc240
                                                                                                   , distFuncCustom  
                                                                                                   , [ 0.1, 70,  1] ] ] # small  palms ( 5..10) cover lot
    , 'med-green2'  : [  0.100, plantLodModels['green-palm']                    ,  0.150,  0.150,  [ noiseFunc240
                                                                                                   , distFuncAdd     
                                                                                                   , [ [ distFuncCustom, [ 0.1, 40, -1] ]
                                                                                                     , [ distFuncCustom, [ 0.1, 60,  1] ]
                                                                                                     , -1.0 ] ] ] # at forest border
    }
    return plantsClass( cameraPos, heightfield, plantmodelDict, TILES, PLANTMAPSIZE, True )

def createHugePlants( cameraPos, cameraRot, heightfield ):
    #print "--- huge sized plants ---"  [commented by Finn]
    # number of tiles which are shown to each side of the player
    TILES = 3
    # the size of 1 tile
    PLANTMAPSIZE = 400
    #    name         : [ chance, model,                                                  minScale, scaleVarianz, [ noise, distribution, [slope, percent, inverse] ]
    # with lod
    plantmodelDict = { 
      'own-palm-med': [  0.010, plantLodModels['wood-palm']                     , 10.000, 10.000,  [ noiseFunc240
                                                                                                   , distFuncCustom 
                                                                                                   , [ 0.1, 60,  1] ] ] # medium palms (10..20) cover 50%
    , 'own-palm-big': [  0.100, plantLodModels['wood-palm']                     , 20.000, 20.000,  [ noiseFunc240
                                                                                                   , distFuncCustom 
                                                                                                   , [ 0.1, 45,  1] ] ] # big    palms (20..40) cover part
    , 'own-palm-hug': [  0.001, plantLodModels['wood-palm']                     , 40.000, 40.000,  [ noiseFunc240
                                                                                                   , distFuncCustom 
                                                                                                   , [ 0.1,  5,  1] ] ] # huge   palms (40..80) are rare
    }
    return plantsClass( cameraPos, heightfield, plantmodelDict, TILES, PLANTMAPSIZE, True )










class plantsClass( DirectObject ):
  def __init__( self, cameraPos, heightfield, plantModelDict, numberOfTiles, sizeOfTile, usingLod ):
    # storage for the nodepaths used by the plants
    self.tileDict = dict()
    
    self.plantClassNp = render.attachNewNode('plants-%i-%i' % (numberOfTiles, sizeOfTile))
    
    
    self.usingLod = usingLod
    # save camera position to create plants around it
    self.cameraPos = cameraPos
    # save a instance of the heightfield to access heightinformations of the ground
    self.heightfield = heightfield
    
    # dictionary with the models
    self.plantModelDict = plantModelDict
    # number of tiles around the camera
    self.numberOfTiles = numberOfTiles
    # size of one of these tiles
    self.sizeOfTile = sizeOfTile
    # sum up all distribution numbers of the plantmodelDict
    self.plantmodelDistributionSum = reduce(add, [self.plantModelDict[key][0] for key in self.plantModelDict.keys()])
    
    # a dictionary to store the models which need to be faded in
    self.fadingNodepaths = dict()
    
    # convert the egg's to bam's
    for name, [c, modelName, minS, maxS, distF] in self.plantModelDict.items():
      try:
        tempModelRoot = loader.loadModel(modelName+".egg")
        tempModelRoot.flattenStrong()
        modelRoot = NodePath('modelRoot-%s' % name)
        tempModelRoot.getChildren().reparentTo(modelRoot)
        modelRoot.flattenStrong()
        modelRoot.writeBamFile(modelName+".bam")
        modelRoot.removeNode()
      # for models with different lod's
      except:
        for [ maxLodDist, minLodDistance, modelName ] in modelName:
          tempModelRoot = loader.loadModel(modelName+".egg")
          tempModelRoot.flattenStrong()
          modelRoot = NodePath('modelRoot-%s' % name)
          tempModelRoot.getChildren().reparentTo(modelRoot)
          modelRoot.flattenStrong()
          modelRoot.writeBamFile(modelName+".bam")
          modelRoot.removeNode()
      
    
    # task to create the plants
    taskMgr.add( self.updatePlants, 'updatePlants' )
    
  # destroy a complete sector
  def deleteSector( self, sectorId ):
#    print "removing sector ", sectorId
    if self.tileDict[sectorId]:
      nodePath = self.tileDict[sectorId]
      if self.fadingNodepaths.has_key( nodePath ):
        del self.fadingNodepaths[nodePath]
      self.tileDict[sectorId].removeNode()
      del self.tileDict[sectorId]
  
  def createSector( self, sectorId ):
    (sectorX, sectorY) = sectorId
#    print "creating sector %s" % str(sectorId)
    # init seed depending on quadrant we are currently in
    random.seed( sectorId )
    
    if self.usingLod:
      plantLodNpChilds = [ NodePath('lod-0-%s' % str(sectorId))
                         , NodePath('lod-1-%s' % str(sectorId)) ]
    else:
      tileNode = self.plantClassNp.attachNewNode('tileNode-%s' % str(sectorId))
      tempTileNode = NodePath('tempTileNode-%s' % str(sectorId))    
    
    # create each plant with the given chance in the sector
    for plantName, [ plantChance
                   , plantModel
                   , plantMinScale
                   , plantScaleVariance
                   , [ noiseFunc, distFunc, distParams ] ] in self.plantModelDict.items():
      # maximum number of plants of this type
      averagePlantCount = (plantChance/100.0) * self.sizeOfTile**2
      # well use ceil, if not some plants may never be generated
      plantCount = math.ceil( averagePlantCount )
      
      distFuncResultAccumulated = 0.0
      #print "planning to create %i plants" % plantCount  [commented by Finn]
      # creating plantCount number of plants
      placedPlantCounter = 0
      for plantId in xrange(int(plantCount)):
        # select position the plant will have
        posX = random.randint(0,self.sizeOfTile) + (sectorX * self.sizeOfTile)
        posY = random.randint(0,self.sizeOfTile) + (sectorY * self.sizeOfTile)
        # get z position of ground
        posZ = self.heightfield.get_elevation([posX, posY])
        # the absolue world position of the plant
        absoluteWorldPosition = Vec3( posX, posY, posZ )
        
        # decide if we want to place this plant based on the noise function
        noiseFuncResult = noiseFunc( posX, posY )
        distFuncResult = distFunc( noiseFuncResult, *distParams )
        # make sure the plants are placed evenly
        distFuncResultAccumulated += distFuncResult
        
        if distFuncResultAccumulated > 1.0:
          placedPlantCounter += 1
          #print "plant"
          # place a plant
          distFuncResultAccumulated -= 1.0
          
          # select scale & rotation of plant
          scale = random.random()
          rotation = random.randint(0,360)
          
          # load model
          if self.usingLod:
            # save that we are using lod, we cant flatten if we are
            #usingLod = True
            # setup lod nodepath
            #plantLodNp = NodePath(FadeLODNode('lod'))
            #plantLodNp.reparentTo(self.plantClassNp)
            i = 0
            for [ maxLodDist, minLodDistance, lodModelName ] in plantModel:
              #print "creating lod %i of plant %s" % (i, plantName)  [commented by Finn]
              tempPlantNp = loader.loadModel(lodModelName+".egg")
              plantNp = NodePath('model-%s' % str(absoluteWorldPosition))
              tempPlantNp.getChildren().reparentTo( plantNp )
              # flatten the nodepath
              #plantNp.flattenStrong()
              # set position of plant
              plantNp.setPos( absoluteWorldPosition )
              plantNp.setHpr( rotation, 0, 0 )
              finalScale = plantMinScale + scale * plantScaleVariance
              plantNp.setScale( finalScale )
              # add plant to tile
              plantNp.reparentTo(plantLodNpChilds[i])
              #
              i += 1
          else:
            #plantNp = loader.loadModelCopy( plantModel+".egg" )
            #plantNp.reparentTo( tileNode )
            tempPlantNp = loader.loadModelCopy( plantModel+".egg" )
            plantNp = NodePath('model-%s' % str(absoluteWorldPosition))
            tempPlantNp.getChildren().reparentTo( plantNp )
            tempPlantNp.removeNode()
            # flatten the nodepath
            #plantNp.flattenStrong()
            # add plant to tile
            plantNp.reparentTo( tempTileNode )
          
            # set position of plant
            plantNp.setPos( absoluteWorldPosition )
            plantNp.setHpr( rotation, 0, 0 )
            finalScale = plantMinScale + scale * plantScaleVariance
            plantNp.setScale( finalScale )
      
      #print "planted %i plants" % placedPlantCounter
    
    # flatten the tile if not using lod
    if self.usingLod:
      plantLodNpChilds[0].flattenStrong()
      plantLodNpChilds[1].flattenStrong()
      
      fadeLodNode = FadeLODNode('lod-%s' % str(sectorId))
      absolutePosX = (sectorX * self.sizeOfTile)+self.sizeOfTile/2.0
      absolutePosY = (sectorY * self.sizeOfTile)+self.sizeOfTile/2.0
      absolutePosZ = self.heightfield.get_elevation([absolutePosX, absolutePosY])
      fadeLodNode.setCenter( Point3( absolutePosX, absolutePosY, absolutePosZ ) )
      
      plantLodNp = NodePath(fadeLodNode)
      #plantLodNp = NodePath('lod-%s' % str(sectorId))
      
      plantLodNpChilds[0].reparentTo( plantLodNp )
      plantLodNp.node().addSwitch( 9999, self.sizeOfTile*2.0 )
      
      plantLodNpChilds[1].reparentTo( plantLodNp )
      plantLodNp.node().addSwitch( self.sizeOfTile*2.0,   0 )
      
      self.tileDict[sectorId] = plantLodNp
      plantLodNp.reparentTo(self.plantClassNp)
      if FADEIN:
        self.makeFadeIn( plantLodNp )
    else:
      # reassign plants to sectorNode
      tempTileNode.getChildren().reparentTo(tileNode)
      tempTileNode.removeNode()
      tempTileNode = None
      #tileNode.flattenMedium()
      tileNode.flattenStrong()
      tileNode.reparentTo( self.plantClassNp )
      self.tileDict[sectorId] = tileNode
      if FADEIN:
        self.makeFadeIn( tileNode )
    #doesnt work with fade in
    #self.plantClassNp.flattenLight()
  
  # update the flora
  def updateTiles( self ):
    # 
    ADD = self.numberOfTiles+1
    SUB = -self.numberOfTiles
    camPos = self.cameraPos.getPos()
    x, y, z = camPos.getX(), camPos.getY(), camPos.getZ()
    
    # find the center sector where the camera is
    startX = (x//self.sizeOfTile)
    startY = (y//self.sizeOfTile)
    
    # get a list of currently active sectors
    # those which are not generated can be deleted
    deleteSectors = self.tileDict.keys()
    
    # calculate the radius we want to show the plants in
    TILES_DISTANCE = (self.numberOfTiles*2+1) * self.sizeOfTile * (1/2.)
    
    # for the tiles around the player
    for xDiff in xrange( SUB, ADD ):
      for yDiff in xrange( SUB, ADD ):
        # calc absolute tile position
        absolute = (absoluteX, absoluteY) = (startX + xDiff, startY + yDiff)
        # create sectory which are around the player, maximum distance
        distance = ( Point3(absoluteX*self.sizeOfTile, absoluteY*self.sizeOfTile, z) - Point3(x,y,z) ).length()
        # only show tiles within the distance
        if distance < TILES_DISTANCE:
          #print "show tile", absolute
          # if not created, create sector
          if not self.tileDict.has_key( absolute ):
            self.createSector( absolute )
          # remove from to delete list
          if absolute in deleteSectors:
            deleteSectors.remove( absolute )
    
    # delete the sectors which are no more needed
    for delSector in deleteSectors:
      self.deleteSector( delSector )

  def updatePlants( self, task ):
    self.updateTiles()
    self.modelFadeIn()
    return Task.cont
  
  
  
  def makeFadeIn( self, nodePath ):
    # make a fade in of the new objects
    # our alpha gradient texture
    transTex = loader.loadTexture('data/textures/transSlider.tif')
    transTex.setWrapU(Texture.WMClamp)
    # our texture stage.  By default it's set to modulate.
    # we give it a high sort value since it needs to be
    #   'above' the rest.
    ts = TextureStage('alpha')
    ts.setSort(1000)
    # apply the texture
    nodePath.setTexture(ts,transTex)
    nodePath.setTexScale(ts,Vec2(0))
    nodePath.setTransparency(1)
    self.fadingNodepaths[nodePath] = [time.time(), ts]
  
  # a task to fade in the models recently created
  def modelFadeIn( self ):
    # update alpha of fading models
    newFadingNodepaths = dict()
    for nodepath, [creationTime, ts] in self.fadingNodepaths.items():
      diffTime = time.time() - creationTime
      if diffTime > 2.0:
        try:
          # model is at no transparency
          # disable transparent texture layer on this nodepath
          nodepath.clearTexture(ts)
        except:
          print "error in if modelFadeIn"
      else:
        try:
          alpha = diffTime/2.0
          #plantNp.setAlphaScale( alpha ) # this is way to slow
          nodepath.setTexOffset(ts,Vec2(alpha))
          # keep the model in the list of models to update
          newFadingNodepaths[nodepath] = [creationTime, ts]
        except:
          print "error in else modelFadeIn"
    self.fadingNodepaths = newFadingNodepaths







