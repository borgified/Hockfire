from lxml import etree
from direct.stdpy.file import *
import shutil
from pandac.PandaModules import Filename
from direct.showbase.DirectObject import DirectObject
'''
Created on 5 Jul 2009

@author: Finn Bryant
'''

NS = "{http://www.quantusgame.org}"

class KeymapController(DirectObject):
	'''
	Create instance of this and it will read key/mouse button presses 
	and spew out in-game controls using the messanger system.
	The controls given depend on which branch of the control tree is currently
	on, e.g. if the player is in a tank then the general.unit.vehicle.ground branch
	so anything anywhere on that branch will be read and sent.
	If a key is defined twice on a branch, the most specific definition is used.
	(general.unit.vehicle beats general.unit).
	
	reads mapping data from main.keymap using the keymap.xsd schema.
	if main.keymap is missing or empty it will copy the data from default.keymap
	
	main.keymap may be changed by the user, keymap.xsd and default.keymap 
	should never be modified by the user.
	(but no checks will be made, so go right ahead if you want...)
	'''
	currentBranch = "general"
	defaultFN = Filename("../data/default.keymap")
	mainFN = Filename("../data/main.keymap")
	schemaFN = Filename("../data/keymap.xsd")
	
	def __init__(self,startingBranch = currentBranch):
		self.currentBranch = startingBranch
		
		if self.schemaFN.exists() == False:
			raise NameError('The File "keymap.xsd" does not appear to exist')
		if self.defaultFN.exists() == False:
			raise NameError('The File "default.keymap" does not appear to exist')
		if self.mainFN.exists() == False:
			shutil.copy(self.defaultFN.toOsSpecific(), self.mainFN.toOsSpecific())
		
		schemaRoot = etree.parse(self.schemaFN.toOsSpecific())
		self.schema = etree.XMLSchema(schemaRoot)
		
		parser = etree.XMLParser(schema = self.schema)
		
		try:
			self.default = etree.parse(self.defaultFN.toOsSpecific(),parser)
		except etree.XMLSyntaxError:
			raise NameError('The File "default.keymap" failed validation.')
		
		try:
			self.main = etree.parse(self.mainFN.toOsSpecific(),parser)
		except etree.XMLSyntaxError, detail:
			if detail.message == "Document is empty, line 1, column 1":
				shutil.copy(self.defaultFN.toOsSpecific(), self.mainFN.toOsSpecific())
				self.main = etree.parse(self.mainFN.toOsSpecific(),parser)
			else:
				raise
		except etree.XMLSyntaxError:
			raise NameError('The File "main.keymap" failed validation.')
		
		branchList = self.getBranchList(self.main.getroot())
		self.mappingList = self.getMappingList(branchList)
		self.setupMappings()
	
	def getBranchList(self, root):
		result = []
		children = root.findall(NS + "branch")
		result.extend(children)
		
		for branch in children:
			result.extend(self.getBranchList(branch))
		
		return result
	
	def getMappingList(self, branchList):
		result = []
		for branch in branchList:
			branchMappings = branch.findall(NS + "mapping")
			element = branch.getparent()
			branchName = branch.get("name")
			while element.tag == NS + "branch":
				branchName = element.get("name") + "." + branchName
				element = element.getparent()
			for mapping in branchMappings:
				result.append((mapping,branchName))
		return result
	
	def setupMappings(self):
		inputsByKey = {}
		for mapping in self.mappingList:
			input = mapping[0].find(NS + "input").text
			controlText = mapping[0].find(NS + "control").text
			
			active = None
			if controlText.endswith("-on"):
				active = True
			elif controlText.endswith("-off"):
				active = False
			
			controlText = controlText.split("-")[0]
			
			if not inputsByKey.has_key(input):
				inputsByKey[input] = []
			
			if active != None:
				inputsByKey[input].append( (mapping[1], controlText, [active]) )
			else:
				inputsByKey[input].append( (mapping[1], controlText, [      ]) )
		
		for input in inputsByKey:
			self.accept(input, self.translate, [inputsByKey[input]])
		
	
	def setCurrentBranch(self,branchName):
		self.currentBranch = branchName
		#print branchName
		self.setupMappings()
	
	def translate(self, controls):
		selectedControl = None
		branchLength = 0
		for control in controls:
			if self.currentBranch.startswith(control[0]):
				if len(control[0]) > branchLength:
					selectedControl = control
					branchLength = len(control[0])
		if selectedControl != None:
			messenger.send(selectedControl[1],selectedControl[2])
			#print selectedControl[0] + "." + selectedControl[1], selectedControl[2]
			