module SocialMod
  class Moderate < Base
  
    # This is a hack to get inheritance working
    cattr_accessor :element_name; @@element_name = 'item'
    cattr_accessor :collection_name; @@collection_name = 'items'
  
    class << self
      # Find all moderated since last sync
      def sync
        find(:all, :from => :sync)
      end
      alias :find_moderated :sync
    
      def find_by_custom_id(id)
        find(:one, :from => :custom, :params => {:id => id})
      end
    end
  
    def flag!
      post(:flag)
    end
  
    def passed?
      verdict == 'passed'
    end
  
    def failed?
      verdict == 'failed' ||
        verdict == 'deferred'
    end
  
    def pending?
      verdict == 'pending'
    end
  
    def destroy
      raise "You can't destroy an item"
    end
    
    def update
      raise "You can't update an item"
    end
  
    class Image < Moderate
      def after_initialize
        self.mime = 'image/jpg'
      end
    end
  
    class Video < Moderate
      def after_initialize
        self.mime = 'video/x-flv'
      end
    end
  
    class Audio < Moderate
      def after_initialize
        self.mime = 'audio/mp3'
      end
    end
  
    class Text < Moderate
      def src=(*a)
        raise "You can't set src for text moderation"
      end
    end
  end
end