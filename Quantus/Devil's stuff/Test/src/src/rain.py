#from direct.particles.ParticleEffect import ParticleEffect
#from direct.particles.Particles import Particles
from direct.particles import ForceGroup, Particles, ParticleEffect #.ForceGroup import ForceGroup
from pandac.PandaModules import PointParticleRenderer, BaseParticleEmitter, BaseParticleRenderer, LinearVectorForce, Vec4, Vec3, Point3

class rainClass( ParticleEffect.ParticleEffect ):
  def __init__( self ):
    ParticleEffect.ParticleEffect.__init__( self )
    self.reset()
    self.setPos(0.000, 0.000, 0.000)
    self.setHpr(0.000, 0.000, 0.000)
    self.setScale(1.000, 1.000, 1.000)
    self.particle = Particles.Particles('particles-1')
    # Particles parameters
    self.particle.setFactory("PointParticleFactory")
    self.particle.setRenderer("LineParticleRenderer")
#    self.particle.setRenderer("PointParticleRenderer")
    self.particle.setEmitter("DiscEmitter")
    self.particle.setPoolSize( 4096 )
    self.particle.setBirthRate( 0.01 )
    self.particle.setLitterSize(10)
    self.particle.setLitterSpread(0)
    self.particle.setSystemLifespan(0.0000)
    self.particle.setLocalVelocityFlag(1)
    self.particle.setSystemGrowsOlderFlag(0)
    # Factory parameters
    self.particle.factory.setLifespanBase(1.5000)
    self.particle.factory.setLifespanSpread(0.0000)
    self.particle.factory.setMassBase(1.0000)
    self.particle.factory.setMassSpread(0.0000)
    self.particle.factory.setTerminalVelocityBase(400.0000)
    self.particle.factory.setTerminalVelocitySpread(0.0000)
    # Point factory parameters
    # Renderer parameters
    self.particle.renderer.setAlphaMode(BaseParticleRenderer.PRALPHANONE)
    self.particle.renderer.setUserAlpha(1.00)
#    # Point parameters
#    self.particle.renderer.setPointSize(1.00)
#    self.particle.renderer.setStartColor(Vec4(1.00, 1.00, 1.00, 1.00))
#    self.particle.renderer.setEndColor(Vec4(1.00, 1.00, 1.00, 1.00))
#    self.particle.renderer.setBlendType(PointParticleRenderer.PPONECOLOR)
#    self.particle.renderer.setBlendMethod(BaseParticleRenderer.PPNOBLEND)
    # Line parameters
#    self.particle.renderer.setHeadColor(Vec4(1.00, 1.00, 1.00, 1.00))
#    self.particle.renderer.setTailColor(Vec4(1.00, 1.00, 1.00, 1.00))
    self.particle.renderer.setHeadColor(Vec4(1.00, 1.00, 1.00, 1.0))
    self.particle.renderer.setTailColor(Vec4(1.00, 1.00, 1.00, 1.0))
    self.particle.renderer.setLineScaleFactor(1.00)
    # Emitter parameters
    self.particle.emitter.setEmissionType(BaseParticleEmitter.ETEXPLICIT)
    self.particle.emitter.setAmplitude(1.0000)
    self.particle.emitter.setAmplitudeSpread(0.0000)
    self.particle.emitter.setOffsetForce(Vec3(0.0000, 0.0000, 0.0000))
    self.particle.emitter.setExplicitLaunchVector(Vec3(0.0000, 0.0000, 0.0000))
    self.particle.emitter.setRadiateOrigin(Point3(0.0000, 0.0000, 0.0000))
    # Disc parameters
    self.particle.emitter.setRadius(1.0000)
    self.addParticles(self.particle)
    self.forceGroup = ForceGroup.ForceGroup('gravity')
    # Force parameters
    force0 = LinearVectorForce(Vec3(0.0000, 0.0000, -1.0000), 1.0000, 0)
    force0.setVectorMasks(1, 1, 1)
    force0.setActive(1)
    self.forceGroup.addForce(force0)
    self.addForceGroup(self.forceGroup)
