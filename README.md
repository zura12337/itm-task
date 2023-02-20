# ITM Task
---

### 🚀 Installation
- `git clone git@github.com:zura12337/itm-task.git`
- `make up`
- `composer install`
- `drush si --existing-config --account-name=admin --account-pass=changeme -y -vvv`

### 🔚 Endpoints
- GET `/api/list` - Returns 3 random sowcase that has not checked `field_feature` field. If you'll add `?featured=1` it will return 3 showcase with `field_feature` field checked.
- GET `/api/single/{id}` - Returns single showcase with given id. it will return an empty object and will throw 404 error if node with given id does not exists.

### 🔒 JWT
- Create Public and Secret keys for Oauth at `/admin/config/people/simple_oauth`
- Create new custom at `/admin/config/services/consumer`
- Send POST request at `/oauth/token` with x-www-form-urlencoded body, for example:
```
grant_type:password
client_id:OMwmwcf6iPpi852rE-SZrSyDNA6aBPGuG2eFLMnc1L4
client_secret:123
password:changeme
username:admin
```
It will return `access_token` and `refresh_token` and you should pass them to REST endpoints in Authorization header

for example:
`Authorization: Bearer {access_token}`
