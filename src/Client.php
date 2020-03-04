<?php
namespace Dungang\DataokeClient;

/**
 * 大淘客API客户端
 */
class Client
{

    /**
     * 公共请求参数
     *
     * @var array
     */
    private $commonParams;

    /**
     * 最后一次请求的地址
     *
     * @var string
     */
    private $lastRequestUrl;

    /**
     * 应用SECRET秘钥
     *
     * @var string
     */
    private $app_secret;

    /**
     * 淘宝客API网关
     *
     * @var string
     */
    private $gateway;

    /**
     * 构造方法
     *
     * @param string $app_key
     *            应用KEY
     * @param string $app_secret
     *            应用SECRET秘钥
     * @param string $gateway
     *            API网关 默认是官方的生成环境地址："https://openapi.dataoke.com/api/";
     */
    public function __construct($app_key, $app_secret, $gateway = "https://openapi.dataoke.com/api")
    {
        // $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->gateway = $gateway;
        $this->commonParams = array(
            "appKey" => $app_key,
            "version" => "v1.2.0"
        );
    }

    /**
     * 超级搜索
     * 大淘客独家推出的超级搜索引擎，优先根据搜索词精选出大淘客商品库中的优质商品，保证商品优质性，同时我们会将联盟搜索中符合搜索词的优质内容挑选出来进行展示，保证搜索结果满足用户需求，提升用户选品概率，非常推荐在CMS等导购网站中使用
     * @param array $params
     * type	搜索类型	是	Number	0-综合结果，1-大淘客商品，2-联盟商品
     * pageId	页码	是	Number	请求的页码，默认参数1
     * pageSize	每页条数	是	Number	默认为20，最大值100
     * keyWords	关键词搜索	是	String	
     * tmall	是否天猫商品	否	Number	1-天猫商品，0-所有商品，不填默认为0
     * haitao	是否海淘商品	否	Number	1-海淘商品，0-所有商品，不填默认为0
     * sort	排序字段	否	String	排序字段信息 销量（total_sales） 价格（price），排序_des（降序），排序_asc（升序）
     * @return array
     */
    public function superSearch($params)
    {
        return $this->execute("/goods/list-super-goods", $params);
    }

    /**
     * 联想词
     * 为搜索提供联想词支持，完善您的搜索功能，建议用户停止输入时进行接口请求
     * keyWords 关键词
     * type 当前搜索API类型：1.大淘客搜索 2.联盟搜索 3.超级搜索
     * @param array` $params
     * @return array
     */
    public function suggestion($params)
    {
        return $this->execute("/goods/search-suggestion", $params);
    }

    /**
     * 品牌库
     * 大淘客收录的品牌相关信息，用户构建品牌专区时，可使用该接口中的相关数据，或通过品牌id在商品列表接口获取相关品牌的所有商品
     * @param array $params
     * pageId	页码	是	String	
     * pageSize	每页条数	否	Number	默认为20，最大值100
     * @return array
     */
    public function brands($params)
    {
        return $this->execute("/tb-service/get-brand-list", $params);
    }

    /**
     * 专辑商品
     * 您可通过精选专辑接口获取专辑ID，再通过专辑商品接口获取该专辑下的所有商品，大淘客对品质的要求始终如一，为淘客及用户带来最优质的选品体验
     * @param array $params
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口第一次返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入库商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * pageSize	每页条数	否	Number	默认为100，大于100按100处理
     * topicId	专辑id	是	Number	通过精选专辑API获取的活动id
     * @return array
     */
    public function topicGoods($params)
    {
        return $this->execute("/goods/topic/goods-list", $params);
    }

    /**
     * 精选专辑
     * 由大淘客数据+人工甄选的优质商品，以主题的方式开放给开发者，您可通过精选专辑接口获取专辑ID，再通过专辑商品接口获取该专辑下的所有商品
     *
     * @return void
     */
    public function topics()
    {
        return $this->execute("/goods/topic/catalogue", []);
    }

    /**
     * 各大榜单
     *
     * @param array $params 
     * rankType	榜单类型	是	Number	1.实时榜 2.全天榜 3.热推榜 4.复购榜 5.热词飙升榜 6.热词排行榜 7.综合热搜榜
     * cid	大淘客一级类目id	否	Number	仅对实时榜单、全天榜单有效
     * @return array
     */
    public function bigRanking($params)
    {
        return $this->execute("/goods/get-ranking-list", $params);
    }

    /**
     * 商品列表
     * 构建商品库，数据入库，建议您首次获取全量商品时调用该接口，若您需要更新商品库中的商品数据（如销量、领券量等）请使用商品更新接口，若您需要获取更多增量商品请通过定时拉取接口获取新的商品信息，若您需要在库中删除失效商品，请使用失效商品接口进行查询后删除
     * @param array $params
     * pageSize	每页条数	否	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * sort	排序方式	否	String	默认为0，0-综合排序，1-商品上架时间从高到低，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * cids	一级类目id	否	String	大淘客的一级分类id，如果需要传多个，以英文逗号相隔，如：”1,2,3”。当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * subcid	二级类目id	否	Number	大淘客的二级类目id，通过超级分类API获取。仅允许传一个二级id，当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * juHuaSuan	是否聚划算	否	Number	1-聚划算商品，0-所有商品，不填默认为0
     * taoQiangGou	是否淘抢购	否	Number	1-淘抢购商品，0-所有商品，不填默认为0
     * tmall	是否天猫商品	否	Number	1-天猫商品，0-所有商品，不填默认为0
     * tchaoshi	是否天猫超市商品	否	Number	1-天猫超市商品，0-所有商品，不填默认为0
     * goldSeller	是否金牌卖家	否	Number	1-金牌卖家，0-所有商品，不填默认为0
     * haitao	是否海淘商品	否	Number	1-海淘商品，0-所有商品，不填默认为0
     * pre	是否预告商品	否	Number	1-预告商品，0-非预告商品
     * brand	是否品牌商品	否	Number	1-品牌商品，0-所有商品，不填默认为0
     * brandIds	品牌id	否	String	当brand传入0时，再传入brandIds将获取不到结果。品牌id可以传多个，以英文逗号隔开，如：”345,321,323”
     * priceLowerLimit	价格（券后价）下限	否	Number	
     * priceUpperLimit	价格（券后价）上限	否	Number	
     * couponPriceLowerLimit	最低优惠券面额	否	Number	
     * commissionRateLowerLimit	最低佣金比率	否	Number	
     * monthSalesLowerLimit	最低月销量	否	Number
     * @return array
     */
    public function goodsList($params)
    {
        return $this->execute("/goods/get-goods-list", $params);
    }

    /**
     * 获取商品详情
     *
     * @param array $params
     * id	商品id	是	Number	大淘客商品id，请求时id或goodsId必填其中一个，若均填写，将优先查找当前单品id
     * goodsId	淘宝商品id	否	String	id或goodsId必填其中一个，若均填写，将优先查找当前单品id
     * @return array
     */
    public function goodsInfo($params)
    {
        return $this->execute("/goods/get-goods-details", $params);
    }

    /**
     * 定时拉取商品
     * 首次通过商品列表获取商品后，后续商品即可使用定时拉取接口进行增量更新，开始时间建议为您上次调用的时间，结束时间为当前时间，可以无重复拉取全库商品
     * @param array $params
     * pageSize	每页条数	否	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * cid	一级类目id	否	String	大淘客的一级分类id。当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * subcid	二级类目id	否	Number	大淘客的二级类目id，通过超级分类API获取。仅允许传一个二级id，当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * pre	是否预告商品	否	Number	1-预告商品，0-非预告商品
     * sort	排序方式	否	String	默认为0，0-综合排序，1-商品上架时间从新到旧，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * startTime	开始时间	否	String	格式为yy-mm-dd hh:mm:ss，商品下架的时间大于等于开始时间
     * endTime	结束时间	否	String	默认为请求的时间，商品下架的时间小于等于结束时间
     * @return array
     */
    public function pullByTime($params)
    {
        return $this->execute("/goods/pull-goods-by-time", $params);
    }

    /**
     * 热搜记录
     * 昨日大淘客CMS端采集统计的前100名搜索热词，可用于您的热搜词榜单，为用户提供搜索建议
     * @param array $params
     * @return array
     */
    public function searchHotWord()
    {
        return $this->execute("/category/get-top100", []);
    }

    /**
     * 商品更新
     * 商品的部分信息会在商品售卖及推广过程中有所变更，您可以通过该接口对销量、领券量等数据进行实时更新
     * @param array $params
     * pageSize	每页条数	是	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * startTime	商品上架开始时间	否	Date	请求格式：yyyy-MM-dd HH:mm:ss
     * endTime	商品上架结束时间	否	Date	请求格式：yyyy-MM-dd HH:mm:ss
     * cids	一级类目Id	否	String	大淘客的一级分类id，如果需要传多个，以英文逗号相隔，如：”1,2,3”。当一级类目id和二级类目id同时传入时，会自动忽略二级类目id，1 -女装，2 -母婴，3 -美妆，4 -居家日用，5 -鞋品，6 -美食，7 -文娱车品，8 -数码家电，9 -男装，10 -内衣，11 -箱包，12 -配饰，13 -户外运动，14 -家装家纺
     * subcid	二级类目Id	否	Number	大淘客的二级类目id，通过超级分类API获取。仅允许传一个二级id，当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * juHuaSuan	是否聚划算	否	Number	1-聚划算商品，0-所有商品，不填默认为0
     * taoQiangGou	是否淘抢购	否	Number	1-淘抢购商品，0-所有商品，不填默认为0
     * tmall	是否天猫商品	否	Number	1-天猫商品，0-所有商品，不填默认为0
     * tchaoshi	是否天猫超市商品	否	Number	1-天猫超市商品，0-所有商品，不填默认为0
     * goldSeller	是否金牌卖家	否	Number	1-金牌卖家，0-所有商品，不填默认为0
     * haitao	是否海淘商品	否	Number	1-海淘商品，0-所有商品，不填默认为0
     * brand	是否品牌商品	否	Number	1-品牌商品，0-所有商品，不填默认为0
     * brandIds	品牌id	否	String	当brand传入0时，再传入brandIds将获取不到结果。品牌id可以传多个，以英文逗号隔开，如：”345,321,323”
     * priceLowerLimit	价格（券后价）下限	否	Number	
     * priceUpperLimit	价格（券后价）上限	否	Number	
     * couponPriceLowerLimit	最低优惠券面额	否	Number	
     * commissionRateLowerLimit	最低佣金比率	否	Number	
     * monthSalesLowerLimit	最低月销量	否	Number	
     * sort	排序字段	否	String	默认为0，0-综合排序，1-商品上架时间从新到旧，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * @return array
     */
    public function updatedGoods($params)
    {
        return $this->execute("/goods/get-newest-goods", $params);
    }

    /**
     * 失效的商品
     * 根据指定时间段返回该时间段内（开始时间和结束时间须为当日）因任何原因在大淘客下架商品，建议您每10分钟更新一次
     * @param array $params
     * pageSize	每页条数	否	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * startTime	开始时间	否	String	默认为yyyy-mm-dd hh:mm:ss，商品下架的时间大于等于开始时间，开始时间需要在当日
     * endTime	结束时间	否	String	默认为请求的时间，商品下架的时间小于等于结束时间，结束时间需要在当日
     * @return array
     */
    public function expiredGoods($params)
    {
        return $this->execute("/goods/get-stale-goods-by-time", $params);
    }

    /**
     * 超级分类
     * 可查询大淘客所有的一级分类和二级分类，且提供一级分类图标及二级分类图标素材，协助您完善商品分类筛选功能的搭建
     * @param array $params
     * @return array
     */
    public function superCategory()
    {
        return $this->execute("/category/get-super-category", []);
    }

    /**
     * 我的收藏
     * 您可以通过该接口查询您在大淘客平台收藏的商品数据
     * @param array $params
     * pageSize	每页条数	否	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * cid	商品在大淘客的分类id	是	String	大淘客的一级分类id，如果需要传多个，以英文逗号相隔，如：”1,2,3”。当一级类目id和二级类目id同时传入时，会自动忽略二级类目id，1 -女装，2 -母婴，3 -美妆，4 -居家日用，5 -鞋品，6 -美食，7 -文娱车品，8 -数码家电，9 -男装，10 -内衣，11 -箱包，12 -配饰，13 -户外运动，14 -家装家纺
     * trailerType	是否返回预告商品	否	Number	（如果为是1，则返回全部商品（包含在线商品），为0只返回在线商品）默认返回全部商品
     * sort	排序字段	否	String	默认为0，0-综合排序，1-商品上架时间从高到低，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * collectionTimeOrder	加入收藏时间	否	Number
     * @return array
     */
    public function collection($params)
    {
        return $this->execute("/goods/get-collection-list", $params);
    }

    /**
     * 我发布的商品
     * 招商淘客可以通过该接口获取所有在自媒体中心提交的商品数据
     * @param array $params
     * pageSize	每页条数	是	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * online	是否下架	否	Number	默认为1，1-未下架商品，0-已下架商品
     * startTime	商品提交开始时间	否	Date	请求格式：yyyy-MM-dd HH:mm:ss
     * endTime	商品上架结束时间	否	Date	请求格式：yyyy-MM-dd HH:mm:ss
     * sort	排序字段	否	String	默认为0，0-综合排序，1-商品上架时间从新到旧，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * @return array
     */
    public function ownerGoods($params)
    {
        return $this->execute("/goods/get-owner-goods", $params);
    }

    /**
     * 活动商品
     * 通过热门活动API获取到相关活动ID后，再使用该接口获取对应的活动商品
     * @param array $params
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口第一次返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入库商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * pageSize	每页条数	否	Number	默认为100，大于100按100处理
     * activityId	活动id	是	Number	通过热门活动API获取的活动id
     * cid	大淘客一级分类ID	否	Number	1 -女装，2 -母婴，3 -美妆，4 -居家日用，5 -鞋品，6 -美食，7 -文娱车品，8 -数码家电，9 -男装，10 -内衣，11 -箱包，12 -配饰，13 -户外运动，14 -家装家纺
     * subcid	大淘客二级分类ID	否	Number	可通过超级分类接口获取二级分类id，当同时传入一级分类id和二级分类id时，以一级分类id为准
     * @return array
     */
    public function activeGoods($params)
    {
        return $this->execute("/goods/activity/goods-list", $params);
    }

    /**
     * 热门活动
     * 开发者可通过热门活动接口获取淘宝官方活动或大淘客官方活动的ID，通过获得的活动ID，使用活动商品接口获取对应的活动商品
     * @param array $params
     * @return array
     */
    public function hotActivies($params)
    {
        return $this->execute("/goods/activity/catalogue", $params);
    }

    /**
     * 高效转链
     * 高效转链接口将您的pid信息、商品地址及优惠券信息进行转链，转链后的结果可进行推广或完成订单，接口支持授权淘宝账号下所有PID进行转链。由于接口特殊性，请适量缓存已转链的链接，以达最佳效率
     * @param array $params
     * goodsId	淘宝商品id	是	String	
     * couponId	优惠券ID	否	String	商品的优惠券ID，一个商品在联盟可能有多个优惠券，可通过填写该参数的方式选择使用的优惠券，请确认优惠券ID正确，否则无法正常跳转
     * pid	推广位ID	否	string	用户可自由填写当前大淘客账号下已授权淘宝账号的任一pid，若未填写，则默认使用创建应用时绑定的pid
     * channelId	渠道id	否	string	用于代理体系，渠道id将会和传入的pid进行验证，验证通过将正常转链，请确认从私域管理系统中提取的渠道id是否正确
     * rebateType	付定返红包	否	Number	0.不使用付定返红包，1.参与付定返红包，付定返红包相关规则：http://www.dataoke.com/info/?id=269
     * @return array
     */
    public function quickGenLink($params)
    {
        return $this->execute("/tb-service/get-privilege-link", $params);
    }

    /**
     * 通过大淘客的商品ID查询猜你喜欢
     * 支持分页
     *
     * @param array $params
     * id	大淘客的商品id	是	Number	
     * size	每页条数	否	Number	默认10 ， 最大值100
     * @return array
     */
    public function guess($params)
    {
        return $this->execute("/goods/list-similer-goods-by-open", $params);
    }

    /**
     * 9.9包邮精选 
     * 接口反馈 
     * 大淘客专业选品团队提供的9.9包邮精选商品，提供最优质的白菜商品列表，您可通过该接口搭建9.9包邮精选等相关特色专区，为用户带来丰富的选品体验
     * 支持分页
     *
     * @param array $params
     * pageSize	每页条数	是	Number	默认为20，最大值100
     * pageId	分页id	是	String	常规分页方式，请直接传入对应页码
     * nineCid	精选类目Id	是	String	9.9精选的类目id，分类id请求详情：-1-精选，1 -居家百货，2 -美食，3 -服饰，4 -配饰，5 -美妆，6 -内衣，7 -母婴，8 -箱包，9 -数码配件，10 -文娱车品
     * @return array
     */
    public function nine($params)
    {
        return $this->execute("/goods/nine/op-goods-list", $params);
    }

    /**
     * 大淘客搜索
     *
     * @param array $params
     * pageSize	每页条数	是	Number	默认为100，最大值200，若小于10，则按10条处理，每页条数仅支持输入10,50,100,200
     * pageId	分页id	是	String	默认为1，支持传统的页码分页方式和scroll_id分页方式，根据用户自身需求传入值。示例1：商品入库，则首次传入1，后续传入接口返回的pageid，接口将持续返回符合条件的完整商品列表，该方式可以避免入口商品重复；示例2：根据pagesize和totalNum计算出总页数，按照需求返回指定页的商品（该方式可能在临近页取到重复商品）
     * keyWords	关键词搜索	是	String	
     * cids	一级类目Id	否	String	大淘客的一级分类id，如果需要传多个，以英文逗号相隔，如：”1,2,3”。当一级类目id和二级类目id同时传入时，会自动忽略二级类目id，一级分类id请求详情：1 -女装，2 -母婴，3 -美妆，4 -居家日用，5 -鞋品，6 -美食，7 -文娱车品，8 -数码家电，9 -男装，10 -内衣，11 -箱包，12 -配饰，13 -户外运动，14 -家装家纺
     * subcid	二级类目Id	否	Number	大淘客的二级类目id，通过超级分类API获取。仅允许传一个二级id，当一级类目id和二级类目id同时传入时，会自动忽略二级类目id
     * juHuaSuan	是否聚划算	否	Number	1-聚划算商品，0-所有商品，不填默认为0
     * taoQiangGou	是否淘抢购	否	Number	1-淘抢购商品，0-所有商品，不填默认为0
     * tmall	是否天猫商品	否	Number	1-天猫商品，0-所有商品，不填默认为0
     * tchaoshi	是否天猫超市商品	否	Number	1-天猫超市商品，0-所有商品，不填默认为0
     * goldSeller	是否金牌卖家	否	Number	1-金牌卖家，0-所有商品，不填默认为0
     * haitao	是否海淘商品	否	Number	1-海淘商品，0-所有商品，不填默认为0
     * brand	是否品牌商品	否	Number	1-品牌商品，0-所有商品，不填默认为0
     * brandIds	品牌id	否	String	当brand传入0时，再传入brandIds将获取不到结果。品牌id可以传多个，以英文逗号隔开，如：”345,321,323”
     * priceLowerLimit	价格（券后价）下限	否	Number	
     * priceUpperLimit	价格（券后价）上限	否	Number	
     * couponPriceLowerLimit	最低优惠券面额	否	Number	
     * commissionRateLowerLimit	最低佣金比率	否	Number	
     * monthSalesLowerLimit	最低月销量	否	Number	
     * sort	排序字段	否	String	默认为0，0-综合排序，1-商品上架时间从新到旧，2-销量从高到低，3-领券量从高到低，4-佣金比例从高到低，5-价格（券后价）从高到低，6-价格（券后价）从低到高
     * @return array
     */
    public function search($params)
    {
        return $this->execute("goods/get-dtk-search-goods", $params);
    }

    /**
     * 咚咚抢 
     *
     * @param array $params
     * roundTime	场次时间	否	Number	默认为当前场次，场次时间输入方式：yyyy-mm-dd hh:mm:ss
     * @return array
     */
    public function qiang($params)
    {
        return $this->execute("/category/ddq-goods-list", $params);
    }

    /**
     * 淘宝客官方搜索接口
     *
     * @param array $params
     * @return array
     */
    public function taobaoTbkDgMaterialOptional($params)
    {
        return $this->execute("/tb-service/get-tb-service", $params);
    }

    /**
     * 获取响应的结果
     *
     * @param ResponseInterface $response
     * @return array|null
     */
    protected function parseResponse($response)
    {
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        }
        return null;
    }


    /**
     * 获取本次请求的url地址
     *
     * @return string
     */
    public function getLastRequestUrl()
    {
        return $this->lastRequestUrl;
    }

    /**
     * 签名参数，再参数列表中添加sign的值
     *
     * @param array $params
     *            待签名的参数
     * @return array
     */
    protected function sign($params)
    {
        unset($params["sign"]);
        $params = \array_filter($params, function ($v, $k) {
            return null != $v && "" != $v;
        }, ARRAY_FILTER_USE_BOTH);
        \ksort($params);
        $kvs = [];
        foreach ($params as $k => &$v) {
            $v = is_array($v) ? implode(",", $v) : $v;
            $kvs[] = $k . "=" . $v;
        }
        $params["sign"] = strtoupper(md5(implode("&", $kvs) . "&key=" . $this->app_secret));
        return $params;
    }

    /**
     * 执行Http Get 请求
     * 应为API的都是读取数据，则主要是GET请求
     *
     * @param array $params
     *            请求的所有参数数组
     * @throws \Exception
     * @return NULL|array
     */
    protected function execute($resource,$params)
    {
        $params = $this->sign(\array_merge($this->commonParams, $params));
        if ($data = $this->requestGet($resource,$params)) {
            if ($data["code"] != 0) {
                $msg = $data["msg"];
                throw new \Exception($msg);
            } else {
                return $data;
            }
        }
        return null;
    }

    private function requestGet($resource,$data = array(), $headers = array())
    {
        $headers = array_merge(array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json;charset=utf-8'
        ), $headers);
        $this->lastRequestUrl = trim($this->gateway,['/']) . $resource . '?' . http_build_query($data);
        $result = null;
        $httpCode = 500;
        if (! function_exists("curl_init")) {
            $ch = curl_init($this->lastRequestUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $_headers = array();
            foreach ($headers as $key => $val) {
                $_headers[] = $key . ': ' . $val . "\r\n";
            }
            $http_response_header = null;
            $result = @file_get_contents($this->lastRequestUrl, false, stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => $_headers
                )
            )));
            if (substr($http_response_header[0], - 2) == 'OK') {
                $httpCode = 200;
            }
        }
        if ($httpCode == 200) {
            return json_decode($result, true);
        }
        return $result;
    }

     /**
     * 将大淘客的商品转换成淘宝客的商品
     */
    public static function convertDataokeToTaobaoke($item)
    {
        //商品信息-佣金类型。MKT表示营销计划，SP表示定向计划，COMMON表示通用计划
        $commission_types = [
            'COMMON', // 0-通用，
            'SP', //1-定向，
            'HIGHT', //2-高佣，//字符串是官方没提供，临时自定义
            'MKT', //3-营销计划
        ];
        return [
            'num_iid' => $item['goodsId'],
            'item_id' => $item['goodsId'],
            'title' => $item['title'],
            'pict_url' => $item['mainPic'],
            'zk_final_price' => $item['originalPrice'],
            'actual_price' => $item['actualPrice'],
            'reserve_price' => $item['originalPrice'] + $item['couponPrice'],
            'user_type' => empty($item['shopType']) ? null : $item['shopType'],
            'item_url' => empty($item['itemLink'])?'':$item['itemLink'],
            'commission_rate' => $item['commissionRate'] * 100,
            'volume' => $item['monthSales'],
            'coupon_start_time' => date('Y-m-d',strtotime($item['couponStartTime'])),
            'coupon_end_time' => date('Y-m-d',strtotime($item['couponEndTime'])),
            'coupon_total_count' => $item['couponTotalNum'],
            'coupon_remain_count' => $item['couponTotalNum'] - $item['couponReceiveNum'],
            'coupon_receive_count' => $item['couponReceiveNum'],
            'coupon_amount' => $item['couponPrice'],
            'coupon_start_fee' => $item['couponConditions'],
            'coupon_share_url' => $item['couponLink'],
            'commission_type' => $commission_types[$item['commissionType']],
            'shop_title' => empty($item['shopName'])?'':$item['shopName'],
            'activity_type' => $item['activityType'],
            'activity_start_time' => empty($item['activityStartTime'])?'':$item['activityStartTime'],
            'activity_end_time' => empty($item['activityEndTime'])?'':$item['activityEndTime'],
        ];
    }
}

