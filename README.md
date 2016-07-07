## Fuzz Laravel Data

Provides model and scope boilerplate for common implementations of Laravel API projects.

### Installation
1. Register the custom Fuzz Composer repository: ```composer config repositories.fuzz composer https://satis.fuzzhq.com``` 
1. Register the composer package: ```composer require fuzz/laravel-data```
1. Create a base model in your project extending `Fuzz\Data\Eloquent\Model`.
```
    <?php
    
    use Fuzz\Data\Eloquent\Model as FuzzModel;
    
    class BaseModel extends FuzzModel
    {
        // ...
    }
```

### DATETIME handling
Timezone juggling is incredible awkward across platforms. To standardize the presentation of database DATETIME fields across interfaces, it is highly recommended that you avail yourself of the provided `mutateDateTimeAttribute` methods and `accessDateTimeAttribute` methods in your models. The accessor will cast all DATETIMEs as UNIX timesetamps, and the mutator provides a more forgiving input interface for DATETIME-ish strings.  
```
    <?php
    
    use Fuzz\Data\Eloquent\Model as FuzzModel;
    
    class BaseModel extends FuzzModel
    {
        public function getCreatedAtAttribute($value)
        {
            return $this->accessDateTimeAttribute($value);
        }
        
        public function setCreatedAtAttribute($value)
        {
            return $this->mutateDateTimeAttribute($value, 'created_at');
        }
        
        public function getUpdatedAtAttribute($value)
        {
            return $this->accessDateTimeAttribute($value);
        }
        
        public function setUpdatedAtAttribute($value)
        {
            return $this->mutateDateTimeAttribute($value, 'updated_at');
        }
        
        // ...
    }
```

### Image Handling
This module uses the [fuzz/laravel-s3 module](https://gitlab.fuzzhq.com/web-modules/laravel-s3) to provide seamless image handling for models. To take advantage of it, be sure to follow the installation and configuration instructions for that package.
 
To enable image handling, mix in the Fuzz\Data\Eloquent\Imageable trait to a model class and implement its methods.

```
    <?php
    
    use Fuzz\Data\Eloquent\Imageable;
    
    class Profile extends BaseModel
    {
        use Imageable;

        protected function getImageDirectory($context = null)
        {
            // "Context" can be used to specify different storage interfaces for different image fields on the same model  
            if ($context === 'primary') {
                return ['users', 'avatars'];
            } elseif ($context === 'secondary') {
                return ['users', 'photos'];
            }
            
            return ['users'];
        }
        
        protected function setAvatarAttribute($value)
        {
            // In this example, laravel-s3 crops avatar images specified by the 'user_avatar' crop
            return $this->mutateImageAttribute($value, 'avatar', 'user_avatar', 'primary');
        }
        
        protected function getAvatarAttribute($value)
        {
            // Form fully-qualified URLs to images correctly
            return $this->accessImageAttribute($value, 'primary');
        }
    }
