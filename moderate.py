# requires pyactiveresource and pyyaml

from pyactiveresource.activeresource import ActiveResource

class Moderate(ActiveResource):
    
  @classmethod
  def set_api_key(cls, key):
    cls.headers = {'Authorization': key}

  @classmethod
  def find_by_custom_id(cls, cid):
    return cls.find_one(custom_id=cid)
    
  @classmethod
  def sync(cls):
    return cls._build_list(
      cls.get('sync')
    )
    
  @classmethod
  def find_moderated(cls):
    return cls.sync()

  def flag(self):
    self.post('flag')

Moderate.site     = 'http://api.socialmod.com'
Moderate.singular = 'item'
Moderate.plural   = 'items'

# Moderate.set_api_key('your_api_key')
# Moderate.find_moderated() #=> [item(1), item(1)]
# 
# m = Moderate()
# m.src = 'http://image.gsfc.nasa.gov/image/image_launch_a5.jpg'
# m.save()