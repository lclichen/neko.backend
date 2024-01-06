# *neko.backend*

## 此处为中国科学技术大学“科大猫咪相簿”小程序[后端代码库](https://git.ustc.edu.cn/ustcat/neko.backend)。
<img width="100" height="100" alt="小程序码" src="https://git.ustc.edu.cn/ustcat/nekoustc/-/raw/master/wxacode.jpg"/>

## 硬件设施

使用了Ucloud的免费对象存储，目前服务器位于阿里云。

## [前端代码库](https://git.weixin.qq.com/wx_wxf2701f15e3f6197e/nekoustc)


## 参考说明

<del>小程序中科普部分参考了PKU的[燕园猫手册小程序](https://github.com/circlelq/miniprogram)中的科普内容。</del>

由于微信认为科普内容是信息资讯，个人小程序无资质，因此删除了小程序内的科普页面。

界面中各类元素使用了[WEUI](https://developers.weixin.qq.com/miniprogram/dev/extended/weui/)，借助[Lin-UI](https://doc.mini.talelin.com/)进行了重构。

对象存储上传功能使用了Ufile的代码进行修改删减。[后端PHP签名代码](https://github.com/ufilesdk-dev/ufile-sdk-auth-server)。

后端用户认证直接使用了微信的[示例](https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/signature.html)，这里是[代码页面](https://res.wx.qq.com/wxdoc/dist/assets/media/aes-sample.eae1f364.zip)。

首页图标使用了[iconfont图标库](https://www.iconfont.cn/collections/detail?spm=a313x.7781069.0.da5a778a4&cid=7634)。

## 开发者
[离离沐雪](https://blog.4c43.work)@USTCAT
千羽律@USTCAT


## 错误代码记录

>10:成功完成  

>1001:传入数据不全  
>1002:登录操作需要重试  
>1003:文件或数据重名  
>1004:名称更改被拒绝  
>1005:数据库记录失败  
>1006:权限验证错误或未登录  
>1007:档案不存在  

## 页面小程序码生成方式

访问地址为 `https://neko.4c43.work/wxacode/wxacode.php`

### 参数配置

|参数|说明|数据范围|
|----|----|----|
|`scene`|用于为小程序页面打开时传参，对猫的页面来说需要传入id|最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~|
|`page`|页面路径，根路径不要加 / 例如 pages/index/index|[可选的路径](#可选页面路径列表)|
|`width`|二维码的宽度，单位 px|默认430，最小 280px，最大 1280px |
|`auto_color`|自动配置线条颜色|如果颜色依然是黑色，则说明不建议配置主色调，默认 false
|`line_color`|auto_color 为 false 时生效，使用 rgb 设置颜色|例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
|`is_hyaline`|是否需要透明底色|为 true 时，生成透明底色的小程序码，默认 false

#### 可选页面路径列表

|路径|页面说明|传入scene|
|----|----|----|
|`pages/cat/cat`|猫的详情页|需要传入scene作为id|
|`pages/index/index`|主页面||
|`pages/list/list`|列表页面||
|`pages/donate/donate`|简单的宣传页||
|`pages/adopt/adopt`|急需领养的列表页||
|`pages/account/account`|账目公示||
|`pages/aboutus/aboutus`|关于我们||

