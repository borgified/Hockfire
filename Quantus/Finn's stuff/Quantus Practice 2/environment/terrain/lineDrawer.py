import math

class LineDrawer():
	
	def __init__(self, image, start, end, width):
		self.image = image
		self.start = start
		self.end = end
		self.dWidth = width
		
		self.change = (self.end[0] - self.start[0], self.end[1] - self.start[1])
		
		self.graphGradient = float(self.change[0])/self.change[1]
		self.gradient = math.atan(self.graphGradient)
		
		self.width = (self.dWidth * math.sin(self.gradient - 0.5 * math.pi), self.dWidth * math.cos(self.gradient - 0.5 * math.pi))
		
		self.startHigh = (self.start[0] + 0.5 * self.width[0], self.start[1] + 0.5 * self.width[1])
		self.startLow  = (self.start[0] - 0.5 * self.width[0], self.start[1] - 0.5 * self.width[1])
		
		self.endHigh   = (self.end[0]   + 0.5 * self.width[0], self.end[1]   + 0.5 * self.width[1])
		self.endLow    = (self.end[0]   - 0.5 * self.width[0], self.end[1]   - 0.5 * self.width[1])
		
		
		self.startGenLine(start) 
		#self.cleanUp(end)
		
		#widthX = self.signum(self.end[0] - self.start[0]) * 0.5 * self.dWidth + 1
		#widthY = self.signum(self.end[1] - self.start[1]) * 0.5 * self.dWidth + 1
		#withinX = (pixel[0] > self.start[0] - widthX) != (pixel[0] > self.end[0] + widthX)
		#withinY = (pixel[1] > self.start[1] - widthY) != (pixel[1] > self.end[1] + widthY)
		
		self.image.setGray(start[0],start[1],1.0)
		self.image.setGray(end[0],end[1],1)
		self.image.gaussianFilter(10)
		
	
	def startGenLine(self, pixel):
		print pixel
		self.setColor(pixel)
		if pixel != self.end:
			if self.graphGradient > 0:
				self.majorGenLine((pixel[0]  ,pixel[1]+1),( 0,+1))
				self.majorGenLine((pixel[0]  ,pixel[1]-1),( 0,-1))
				self.majorGenLine((pixel[0]+1,pixel[1]  ),(+1, 0))
				self.majorGenLine((pixel[0]-1,pixel[1]  ),(-1, 0))
			else:
				self.majorGenLine((pixel[0]  ,pixel[1]+1),( 0,+1))
				self.majorGenLine((pixel[0]  ,pixel[1]-1),( 0,-1))
				self.majorGenLine((pixel[0]+1,pixel[1]  ),(+1, 0))
				self.majorGenLine((pixel[0]-1,pixel[1]  ),(-1, 0))
	
	def majorGenLine(self, pixel, direction):
		#print pixel
		self.setColor(pixel)
		#widthX = self.signum(self.end[0] - self.start[0]) * 0.5 * self.dWidth + 1
		#widthY = self.signum(self.end[1] - self.start[1]) * 0.5 * self.dWidth + 1
		#withinX = (pixel[0] > self.start[0] - widthX) != (pixel[0] > self.end[0] + widthX)
		#withinY = (pixel[1] > self.start[1] - widthY) != (pixel[1] > self.end[1] + widthY)
		if True:#withinX & withinY:
			right = self.turnRight(direction)
			if self.withinLine((pixel[0] + direction[0], pixel[1] + direction[1])):
				if self.withinLine((pixel[0] + right[0], pixel[1] + right[0])):
					self.line((pixel[0] + right[0], pixel[1] + right[1]),right)
				self.majorGenLine((pixel[0] + direction[0], pixel[1] + direction[1]),direction)
			elif self.withinLine((pixel[0] + right[0], pixel[1] + right[0])):
				self.majorGenLine((pixel[0] + right[0], pixel[1] + right[1]),direction)
			elif self.withinLine((pixel[0] + right[0] - direction[0], pixel[1] + right[1] - direction[1])):
				self.majorGenLine((pixel[0] + right[0] - direction[0], pixel[1] + right[1] - direction[1]),direction)
			
	def line(self, pixel, direction):
		self.setColor(pixel)
		if self.withinLine((pixel[0] + direction[0], pixel[1] + direction[1])):
			self.line((pixel[0] + direction[0], pixel[1] + direction[1]),direction)
	
	def turnRight(self,direction):
		return (direction[1] , direction[0] * -1)
	
	def withinLine(self, pixel):
		result = True
		if not self.startHigh[1] == self.endHigh[1]:
			if  self.isBelow(pixel,self.startHigh,self.endHigh ) == self.isBelow(pixel, self.startLow ,self.endLow  ):
				result = False
		if not self.startHigh[1] == self.startLow[1]:
			if  self.isBelow(pixel,self.startHigh,self.startLow) == self.isBelow(pixel, self.endHigh  ,self.endLow  ):
				result = False
		return result
	
	def isBelow( self , pixel , start , end ):
		lineVec = (end[0] - start[0],end[1] - start[1])
		
		pixelVec = (pixel[0] - start[0],pixel[1] - start[1])
		ratio = float(pixelVec[1]) / lineVec[1]
		
		if ratio * lineVec[0] > pixelVec[0]:
			return True
		else:
			return False
	
	def setColor(self,pixel):
		if self.withinLine(pixel):
			pass
			self.image.setGray(pixel[0],pixel[1],0.6)
		else:
			pass
			#self.image.setGray(pixel[0],pixel[1],0.4)
	
	def signum(self, int):
		if int < 0:
			return -1
		elif int > 0:
			return 1
		else:
			return 0
