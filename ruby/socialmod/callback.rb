module SocialMod
  class Callback
    attr_reader :params,
                :id,
                :timestamp,
                :custom_id,
                :state,
                :signature,
                :time
                
    def initialize(params)
      @params    = params
      @id        = params['id']
      @timestamp = params['timestamp']
      @custom_id = params['custom_id']
      @state     = params['state']
      @signature = params['signature']
      @time      = Time.at(@timestamp.to_i)
    end
    
    def valid?
      hmac == @signature
    end
    
    def passed?
      state == 'passed'
    end
  
    def failed?
      state == 'failed'
    end
    
    def deferred?
      state == 'deferred'
    end
    
    protected
      def api_key
        SocialMod::Base.api_key || raise('Must provide API key')
      end
      
      def hmac
        str = [@timestamp, @id, @state].join('')
        @hmac_digest ||= OpenSSL::Digest::Digest.new('SHA1')
        OpenSSL::HMAC.hexdigest(@hmac_digest, api_key, str)
      end
  end
end