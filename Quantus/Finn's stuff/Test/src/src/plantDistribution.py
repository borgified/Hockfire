# plant distribution
import math



# a function to get the sign of a float
def sign( number ):
  if number < 0.0:
    return -1
  else:
    return 1

# ---- plant distribution functions ------

def distFunction1vlo( noiseFuncResult ):
  t0 = noiseFuncResult-0.50
  noiseFuncResultSign = sign(t0)
  t1 = math.fabs(t0) ** 0.1
  t2 = t1 * noiseFuncResultSign
  return t2
def distFunction1lo( noiseFuncResult ):
  t0 = noiseFuncResult-0.35
  noiseFuncResultSign = sign(t0)
  t1 = math.fabs(t0) ** 0.1
  t2 = t1 * noiseFuncResultSign
  return t2
def distFunction1( noiseFuncResult ):
  noiseFuncResultSign = sign(noiseFuncResult)
  t1 = (math.fabs(noiseFuncResult)) ** 0.1
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2
def distFunction1hi( noiseFuncResult ):
  t0 = noiseFuncResult+0.35
  noiseFuncResultSign = sign(t0)
  t1 = math.fabs(t0) ** 0.1
  t2 = t1 * noiseFuncResultSign
  return t2
def distFunction1vhi( noiseFuncResult ):
  t0 = noiseFuncResult+0.50
  noiseFuncResultSign = sign(t0)
  t1 = math.fabs(t0) ** 0.1
  t2 = t1 * noiseFuncResultSign
  return t2
def distFunction1ctr( noiseFuncResult ):
  return distFunction1hi(noiseFuncResult) - distFunction1lo(noiseFuncResult) - 1.0

# ---- 

def distFunction2vlo( noiseFuncResult ):
  t0 = noiseFuncResult - 0.50
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2
def distFunction2lo( noiseFuncResult ):
  t0 = noiseFuncResult - 0.35
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2
def distFunction2( noiseFuncResult ):
  t0 = noiseFuncResult
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2
def distFunction2hi( noiseFuncResult ):
  t0 = noiseFuncResult + 0.35
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2
def distFunction2vhi( noiseFuncResult ):
  t0 = noiseFuncResult + 0.50
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2

# -----

def distFuncHardBorder( noiseFuncResult, percent ):
  t0 = noiseFuncResult+((percent-50.0)*0.02)
  noiseFuncResultSign = sign(t0)
  t1 = math.fabs(t0) ** 0.1
  t2 = t1 * noiseFuncResultSign
  return t2

def distFuncSmoothBorder( noiseFuncResult, percent ):
  t0 = noiseFuncResult+((percent-50.0)*0.02)
  noiseFuncResultSign = sign(t0)
  t1 = (math.fabs(t0)) ** 2.0
  t2 = (t1 * noiseFuncResultSign * 1.0)
  return t2

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

# not needed
def distFuncSub( noiseFuncResult, data1, data2, add ):
  func1, params1 = data1
  func2, params2 = data2
  res = func1( noiseFuncResult, *params1) - func2( noiseFuncResult, *params2) + add
  return res



#def distFunction4( noiseFuncResult ):
#  return distFunction5inv( noiseFuncResult ) - distFunction5( noiseFuncResult ) + 1.0
#def distFunction5( noiseFuncResult ):
#  # has a peek in the center of the distribution
#  t1 = (math.fabs(noiseFuncResult)) ** 0.3
#  t2 = ((t1 * 1.0) - 0.5) * 2.0
#  return t2
#def distFunction6( noiseFuncResult ):
#  return (noiseFuncResult+1.0)/2.0
#def distFunction7( noiseFuncResult ):
#  return -(noiseFuncResult+1.0)/2.0


def distFunctionSet1( noiseFuncResult ):
  return 1.0


# --- FUNCTION TO ANALYZE THE PLANT DISTRIBUTION ---
#      generates images out of these functions
def distFuncAnalyze( distFunction, parameters=[], nameAddon='' ):
  # image settings
  imgSize = 256
  imgRange = 2.0 # 2 -> x from -2 to 2 & y from -2 to 2
  # conversion functions
  def xToVal( x ):
    val =  ((x/float(imgSize))-0.5)*2*imgRange
    return val
  def valToY( val ):
    val = (outVal+imgRange)/(imgRange*2.0)*(imgSize)
    y = 255 - int(max( min( val, imgSize-1), 0))
    return y
  
  # create image
  img = Image.new('RGBA', (imgSize,imgSize))
  # init drawing
  draw = ImageDraw.Draw(img)
  # draw a cross in the center of the image
  draw.line((imgSize/2.0,0,imgSize/2.0,imgSize), (128,128,128))
  draw.line((0,imgSize/2.0,imgSize,imgSize/2.0), (128,128,128))
  # draw a square at -1..1
  draw.line( ( (imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0)) )
           , (192,192,192) )
  draw.line( ( (imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0)) )
           , (192,192,192) )
  draw.line( ( imgSize-(imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0)) )
           , (192,192,192) )
  draw.line( ( imgSize-(imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0))
             , (imgSize/(imgRange*2.0))
             , imgSize-(imgSize/(imgRange*2.0)) )
           , (192,192,192) )
  oldPos = None
  
  for x in xrange(imgSize):
    outVal = distFunction( xToVal( x ), *parameters )
    y = valToY( outVal )
    # if has old pos -> start drawing line
    if oldPos:
      draw.line(oldPos+[x,y], (0,0,0))
      oldPos = [x,y]
    # if no old pos -> save position for later drawing
    else:
      oldPos = [x,y]
  del draw
  
  filename = 'distFuncTest/function-%s-%s.png' % (str(distFunction.__name__), nameAddon)
  img.save(filename)
  print "generated %s" % filename

# generate images of the functions
if __name__ == "__main__":
  print "testing functions"
  print "  it will generate function graphs for the vegetation functions"
  try:
    from PIL import Image, ImageDraw
  except:
    print "this function requires PIL (python image library)"
  distFuncAnalyze( distFunction1 )
  distFuncAnalyze( distFunction1lo )
  distFuncAnalyze( distFunction1hi )
  distFuncAnalyze( distFunction1vhi )
  distFuncAnalyze( distFunction1vlo )
  distFuncAnalyze( distFunction1ctr )
  
  distFuncAnalyze( distFunction2 )
  distFuncAnalyze( distFunction2hi )
  distFuncAnalyze( distFunction2lo )
  distFuncAnalyze( distFunction2vlo )
  distFuncAnalyze( distFunction2vhi )

  '''for i in xrange(6):
    val = i * 20
    #distFuncAnalyze( distFuncHardBorder, [val], str(val) )
    #distFuncAnalyze( distFuncSmoothBorder, [val], str(val) )
    for s in xrange(-4,5):
      strength = 4**(s/4.0)
      distFuncAnalyze( distFuncCustom, [strength, val], str("%s-%s" % (strength,val)) )
      distFuncAnalyze( distFuncAdder, [strength, val], str("%s-%s" % (strength,val)) )'''
  
  params =  [ [ distFuncCustom, [ 0.1, 10,-1] ]
            , [ distFuncCustom, [ 0.1, 90, 1] ]
            , -1.0 ]
  distFuncAnalyze( distFuncCustom, params[0][1], str(params[0]) )
  distFuncAnalyze( distFuncCustom, params[1][1], str(params[1]) )
  distFuncAnalyze( distFuncAdd, params, 'adder' )
  
  
  
  
  
  
  
  