# *neko.backend*

## 此处为中国科学技术大学“科大猫咪相簿”小程序[后端代码库](https://git.ustc.edu.cn/ustcat/neko.backend)。

重构科大猫咪相簿后端，并尝试采取Git方式向服务器同步，总体来看对开发过程有很大的改善。Git天下第一！

## 硬件设施

使用了Ucloud的免费对象存储，目前服务器位于腾讯云。

## [前端代码库](https://git.weixin.qq.com/wx_wxf2701f15e3f6197e/nekoustc)


## 参考说明

小程序中科普部分参考了PKU的[燕园猫手册小程序](https://github.com/circlelq/miniprogram)中的科普内容。

界面中各类元素使用了[WEUI](https://developers.weixin.qq.com/miniprogram/dev/extended/weui/)，借助[Lin-UI](https://doc.mini.talelin.com/)进行了重构。

对象存储上传功能使用了Ufile的代码进行修改删减。[后端PHP签名代码](https://github.com/ufilesdk-dev/ufile-sdk-auth-server)。

后端用户认证直接使用了微信的[示例](https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/signature.html)，这里是[代码页面](https://res.wx.qq.com/wxdoc/dist/assets/media/aes-sample.eae1f364.zip)。

首页图标使用了[iconfont图标库](https://www.iconfont.cn/collections/detail?spm=a313x.7781069.0.da5a778a4&cid=7634)。

## 开发者
[离离沐雪](https://blog.4c43.work)@USTCAT
千羽律@USTCAT