### 2.0.0 (2017-11-22)
   #### 需求功能
     * 修改车列表筛选
     * 车搜索框搜索
   #### controller
     * 修改了 /v4/car.php /v4/publish.php  上传车辆-上牌地点 基本配置重写 
     * 修改了 controller/car.php 高德地图
   #### model
     * 修改了 CarSellingV1.php CarSellingExtraInfo.php
   #### mysql
     * bibi_car_selling_list car_source (上传时加个car_source)       
     * bibi_car_brand_series car_type 车级别
     * bibi_car_selling_list_info  
     * bibi_car_model_detail 座位数(把个去掉) 排量(把L去掉) 燃油类别(加了个type) 环保标准(加了个type) 变速箱(加了个type)
     



