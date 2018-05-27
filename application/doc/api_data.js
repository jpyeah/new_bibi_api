define({ "api": [  {    "type": "POST",    "url": "/app/getstartimg",    "title": "获取启动页图片",    "name": "APP_getstartimg",    "group": "APP",    "description": "<p>获取启动页图片</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn\n*"      }    ],    "parameter": {      "examples": [        {          "title": "请求样例",          "content": "POST /app/getstartimg",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/App.php",    "groupTitle": "APP"  },  {    "type": "POST",    "url": "/app/register",    "title": "注册App(获取device_identifier)",    "name": "APP_getversion",    "group": "APP",    "description": "<p>注册App</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_id",            "description": "<p>版本号</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_resolution",            "description": "<p>版本号</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_sys_version",            "description": "<p>版本号</p>"          },          {            "group": "Parameter",            "type": "number",            "optional": false,            "field": "device_type",            "description": "<p>1:ios 2:android</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /app/register\n{\n  \"data\": {\n    \"device_id\":\"\",\n    \"device_resolution\":\"\",\n   \"device_sys_version\":\"\",\n    \"device_type\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/App.php",    "groupTitle": "APP"  },  {    "type": "POST",    "url": "/app/getversion",    "title": "获取最新版本号",    "name": "APP_getversion",    "group": "APP",    "description": "<p>获取最新版本号</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "version_code",            "description": "<p>版本号</p>"          },          {            "group": "Parameter",            "type": "number",            "optional": false,            "field": "type",            "description": "<p>1:ios 2:android</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /app/getversion\n{\n  \"data\": {\n    \"version_code\":\"\",\n    \"type\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/App.php",    "groupTitle": "APP"  },  {    "type": "POST",    "url": "/app/suggest",    "title": "意见反馈",    "name": "APP_suggest",    "group": "APP",    "description": "<p>意见反馈</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>用户session</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备device</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "description",            "description": "<p>反馈意见</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /app/suggest\n{\n  \"data\": {\n    \"description\":\"\",\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/App.php",    "groupTitle": "APP"  },  {    "type": "POST",    "url": "/app/uploadtoken",    "title": "获取七牛token",    "name": "APP_uploadtoken",    "group": "APP",    "description": "<p>获取token</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>device设备标识</p>"          }        ]      }    },    "version": "0.0.0",    "filename": "controllers/App.php",    "groupTitle": "APP"  },  {    "type": "POST",    "url": "/app/sendCode",    "title": "发送验证码",    "name": "App_send_mobile",    "group": "App",    "description": "<p>发送验证码</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "mobile",            "description": "<p>手机号码</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/sendCode\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"mobile\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "controllers/App.php",    "groupTitle": "App"  },  {    "type": "POST",    "url": "/Car/brand",    "title": "获取品牌",    "name": "Car_brand",    "group": "Car",    "description": "<p>获取品牌</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "examples": [        {          "title": "请求样例",          "content": "POST /Car/brand\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \n    \n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/Car.php",    "groupTitle": "Car"  },  {    "type": "POST",    "url": "/Car/model",    "title": "获取车型",    "name": "Car_mode",    "group": "Car",    "description": "<p>获取系列</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "number",            "optional": false,            "field": "series_id",            "description": "<p>系列id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "GET /Car/model\n{\n  \"data\": {\n    \"series_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "success": {      "fields": {        "Success 200": [          {            "group": "Success 200",            "type": "string",            "optional": false,            "field": "interior",            "description": "<p>内饰</p>"          },          {            "group": "Success 200",            "type": "string",            "optional": false,            "field": "exterior",            "description": "<p>外饰</p>"          },          {            "group": "Success 200",            "type": "string",            "optional": false,            "field": "model_url",            "description": "<p>图片链接</p>"          }        ]      }    },    "version": "0.0.0",    "filename": "controllers/Car.php",    "groupTitle": "Car"  },  {    "type": "POST",    "url": "/Car/series",    "title": "获取系列",    "name": "Car_series",    "group": "Car",    "description": "<p>获取系列</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "number",            "optional": true,            "field": "brand_id",            "description": "<p>品牌id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /Car/series\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"brand_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/Car.php",    "groupTitle": "Car"  },  {    "type": "POST",    "url": "/v1/car/index",    "title": "车型车辆详情",    "name": "car_index",    "group": "Car",    "description": "<p>车型车辆详情</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "model_id",            "description": "<p>车型Id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": " POST /v1/car/index\n{\n  \"data\": {\n    \"session_id\":\"\",\n    \"model_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/Car.php",    "groupTitle": "Car"  },  {    "type": "POST",    "url": "/v1/car/series",    "title": "车辆系列表(首页)",    "name": "car_series",    "group": "Car",    "description": "<p>车辆系列表(首页)</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "page",            "description": "<p>页码 从0开始</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": " POST /v1/car/seires\n{\n  \"data\": {\n    \"page\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/Car.php",    "groupTitle": "Car"  },  {    "type": "POST",    "url": "/v1/collect/create",    "title": "添加收藏",    "name": "collect_create",    "group": "Collect",    "description": "<p>添加收藏</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "car_id",            "description": "<p>车辆Id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/collect/create\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"car_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Collect.php",    "groupTitle": "Collect"  },  {    "type": "POST",    "url": "/v1/collect/delete",    "title": "删除收藏",    "name": "collect_delete",    "group": "Collect",    "description": "<p>删除收藏</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "car_id",            "description": "<p>车辆Id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/collect/delete\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"car_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Collect.php",    "groupTitle": "Collect"  },  {    "type": "POST",    "url": "/v1/collect/list",    "title": "收藏列表",    "name": "collect_list",    "group": "Collect",    "description": "<p>收藏列表</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "page",            "description": "<p>页码</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/collect/list\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"page\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Collect.php",    "groupTitle": "Collect"  },  {    "type": "POST",    "url": "/v1/focus/create",    "title": "添加车关注",    "name": "focus_create",    "group": "Focus",    "description": "<p>添加关注</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "brand_id",            "description": "<p>品牌id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/focus/create\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"brand_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Focus.php",    "groupTitle": "Focus"  },  {    "type": "POST",    "url": "/v1/focus/delete",    "title": "删除关注",    "name": "focus_delete",    "group": "Focus",    "description": "<p>删除关注</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "focus_id",            "description": "<p>focus_id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/focus/delete\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"focus_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Focus.php",    "groupTitle": "Focus"  },  {    "type": "POST",    "url": "/v1/focus/list",    "title": "关注列表",    "name": "focus_list",    "group": "Focus",    "description": "<p>关注列表</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "number",            "optional": true,            "field": "user_id",            "description": "<p>用户id any id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/focus/list\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Focus.php",    "groupTitle": "Focus"  },  {    "type": "POST",    "url": "/v1/order/create",    "title": "创建订单",    "name": "order_create",    "group": "Order",    "description": "<p>创建订单</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "car_id",            "description": "<p>车辆ID</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "contact_name",            "description": "<p>联系人姓名</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "contact_phone",            "description": "<p>联系人电话</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "order_amount",            "description": "<p>订单总额</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "sub_fee",            "description": "<p>订金</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/order/create\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"car_id\":\"\",\n    \"contact_name\":\"\",\n    \"contact_phone\":\"\",\n    \"order_amount\":\"\",\n    \"sub_fee\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Order.php",    "groupTitle": "Order"  },  {    "type": "POST",    "url": "/v1/order/index",    "title": "订单详情",    "name": "order_index",    "group": "Order",    "description": "<p>订单详情</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "order_id",            "description": "<p>订单ID</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/order/index\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"order_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Order.php",    "groupTitle": "Order"  },  {    "type": "POST",    "url": "/v1/order/list",    "title": "订单列表",    "name": "order_list",    "group": "Order",    "description": "<p>订单列表</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "page",            "description": "<p>页码</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "order_status",            "description": "<p>订单状态  1: 待签约2:运输中3:已到店 4:已关闭</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/order/list\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"page\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "modules/V1/controllers/Order.php",    "groupTitle": "Order"  },  {    "type": "POST",    "url": "/Car/extrainfo",    "title": "获取基本配置",    "name": "car_extrainfo",    "group": "Publish",    "description": "<p>获取基本配置</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /Car/extrainfo\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n  }\n}",          "type": "json"        }      ]    },    "version": "0.0.0",    "filename": "controllers/Car.php",    "groupTitle": "Publish"  },  {    "type": "POST",    "url": "/v1/user/bindmobile",    "title": "绑定手机号",    "name": "User_bandmobile",    "group": "User",    "description": "<p>绑定手机号</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>device_identifier</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "mobile",            "description": "<p>手机号码</p>"          },          {            "group": "Parameter",            "type": "number",            "optional": false,            "field": "code",            "description": "<p>验证码</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/user/bindmobile\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"mobile\":\"\",\n    \"code\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/user/oauthbindmobile",    "title": "第三方登录绑定手机号",    "name": "User_oauthbindmobile",    "group": "User",    "description": "<p>第三方登录绑定手机号</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "version": "2.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>device_identifier</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "mobile",            "description": "<p>手机号码</p>"          },          {            "group": "Parameter",            "type": "number",            "optional": false,            "field": "code",            "description": "<p>验证码</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/user/oauthbindmobile\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n    \"mobile\":\"\",\n    \"code\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/chattoken",    "title": "融云token刷新",    "name": "user_chattoken",    "group": "User",    "description": "<p>融云消息刷新</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "session_id",            "description": "<p>session_id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/chattoken\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/homepage",    "title": "个人中心",    "name": "user_homepage",    "group": "User",    "description": "<p>个人中心</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": false,            "field": "session_id",            "description": "<p>session_id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/homepage\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/oauthlogin",    "title": "第三方登录",    "name": "user_oauthlogin",    "group": "User",    "description": "<p>第三方登录</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://testapi.bibicar.cn"      }    ],    "version": "2.0.0",    "parameter": {      "fields": {        "request": [          {            "group": "request",            "type": "string",            "optional": false,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "request",            "type": "string",            "optional": true,            "field": "wx_open_id",            "description": "<p>微信识别ID</p>"          },          {            "group": "request",            "type": "string",            "optional": true,            "field": "weibo_open_id",            "description": "<p>微博识别ID</p>"          },          {            "group": "request",            "type": "string",            "optional": false,            "field": "nickname",            "description": "<p>昵称</p>"          },          {            "group": "request",            "type": "string",            "optional": false,            "field": "avatar",            "description": "<p>头像</p>"          }        ],        "response": [          {            "group": "response",            "type": "number",            "optional": false,            "field": "is_bind_mobile",            "description": "<p>是否绑定手机 1：是 2：否</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/oauthlogin\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/profile",    "title": "用户信息",    "name": "user_profile",    "group": "User",    "description": "<p>用户信息</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "session_id",            "description": "<p>session_id</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/profile\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"session_id\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/quicklogin",    "title": "用户登录/注册",    "name": "user_quicklogin",    "group": "User",    "description": "<p>用户登录/注册</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "mobile",            "description": "<p>手机号码</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "code",            "description": "<p>验证码</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/quicklogin\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"mobile\":\"\",\n    \"code\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  },  {    "type": "POST",    "url": "/v1/User/updateProfile",    "title": "用户资料更新",    "name": "user_updateProfile",    "group": "User",    "description": "<p>用户资料更新</p>",    "permission": [      {        "name": "anyone"      }    ],    "sampleRequest": [      {        "url": "http://new.bibicar.cn"      }    ],    "version": "1.0.0",    "parameter": {      "fields": {        "Parameter": [          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "device_identifier",            "description": "<p>设备唯一标识</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "session_id",            "description": "<p>session_id</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "key",            "description": "<p>键值 nickname birth avatar gender signature</p>"          },          {            "group": "Parameter",            "type": "string",            "optional": true,            "field": "value",            "description": "<p>值</p>"          }        ]      },      "examples": [        {          "title": "请求样例",          "content": "POST /v1/User/updateProfile\n{\n  \"data\": {\n    \"device_identifier\":\"\",\n    \"key\":\"\",\n    \"value\":\"\",\n\n\n  }\n}",          "type": "json"        }      ]    },    "filename": "modules/V1/controllers/User.php",    "groupTitle": "User"  }] });
