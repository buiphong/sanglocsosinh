<?php
/**
 * Class xử lý các vấn đề liên quan tới gian hàng
 */
class ShopComponentController extends Presentation
{
    /**
     * Danh sách nhà vườn (Thành viên)
     */
    public function listMemberAction()
    {
        $this->loadModule('ECProductCP');
        $list = Models_ECProduct::getListMemberProduct();
        if($list)
        {
            foreach($list as $item)
            {
                //Link tới trang danh sách gian hàng của thành viên
                if(empty($item['fullname']))
                    $item['fullname'] = $item['username'];
                $this->tpl->assign('linkShop', $this->url->action('listShop', array('member' => $item['id'])));
                $this->tpl->insert_loop('main.member', 'member', $item);
            }
        }
        return $this->view();
    }

    /**
     * Danh sách gian hàng, cho phép lọc theo thành viên hoặc không
     */
    public function listShopAction()
    {
        $this->loadModule('ECShopCP');
        $model = new Models_ECShop();
        $model->db->select('id,name,member_id,image_path,view,content_attr,
            (select count(id) from ec_shop_product where shop_id=ec_shop.id) as totalProduct');
        if(isset($this->params['member']) && !empty($this->params['member']))
            $model->db->where('member_id', $this->params['member']);
        $shops = $model->db->orderby('created_time', 'desc')->getcFieldsArray();
        $i = 1;
        foreach($shops as $shop)
        {
            //Get province shop
            $attr = unserialize($shop['content_attr']);
            $attr = Models_ECShopAttribute::getArrayCodeAttr($attr);
            $shop['attr'] = $attr;
            $shop['link'] = $this->url->action('detailShop', array('shop' => $shop['id']));
            $shop['class'] = 'mr5';
            if(($i % 3) == 0)
            {
                $shop['class'] = '';
                $this->tpl->parse('main.shop.clear');
            }
            $this->tpl->insert_loop('main.shop', 'shop', $shop);
            $i++;
        }
        return $this->view();
    }

    /**
     * danh sách gian hàng xem nhiều
     */
    public function topViewShopAction()
    {
        $this->loadModule('ECShopCP');
        $limit = @$this->params['limit'];
        if(!$limit)
            $limit = 10;
        $model = new Models_ECShop();
        $model->db->select('id,name,member_id,image_path,view,content_attr,
        (select count(id) from ec_shop_product where shop_id=ec_shop.id) as totalProduct');
        if(isset($this->params['member']) && !empty($this->params['member']))
            $model->db->where('member_id', $this->params['member']);
        $shops = $model->db->orderby('view', 'desc')->limit(10)->getcFieldsArray();
        foreach($shops as $shop)
        {
            //Get province shop
            $attr = unserialize($shop['content_attr']);
            $attr = Models_ECShopAttribute::getArrayCodeAttr($attr);
            $shop['attr'] = $attr;
            $shop['link'] = $this->url->action('detailShop', array('shop' => $shop['id']));
            $this->tpl->insert_loop('main.shop', 'shop', $shop);
        }
        return $this->view();
    }

    /**
     * Danh sách shop sắp xếp
     */
    public function listSpecialShopAction()
    {
        if(isset($this->params['type']))
        {
            $this->loadModule('ECShopCP');
            $type = Models_ECShopSType::getById($this->params['type']);
            $this->tpl->assign('type', $type);
            $limit = @$this->params['limit'];
            if(!$limit)
                $limit = 10;
            $list = Models_ECShopSpecial::getList($this->params['type'], $limit);
            foreach($list as $shop)
            {
                $shop['content_attr'] = unserialize($shop['content_attr']);
                $shop['attr'] = Models_ECShopAttribute::getArrayCodeAttr($shop['content_attr']);
                $this->tpl->insert_loop('main.shop', 'shop', $shop);
            }
        }
        return $this->view();
    }

    /**
     * Hiển thị danh sách gian hàng của thành viên
     */
    public function listShopMemberAction()
    {
        $this->loadModule('ECShopCP');
        if(isset($_SESSION['member']['id']))
        {
            $shops = Models_ECShop::getShopMember($_SESSION['member']['id']);
            $num = 1;
            foreach($shops as $shop)
            {
                $attrs = unserialize($shop['content_attr']);
                if(!empty($attrs['start_time']))
                    $shop['start_date'] = date('d/m/Y', strtotime($attrs['start_time']));
                if(!empty($attrs['start_time']) && !empty($attrs['month_num']))
                    $shop['end_date'] = date('d/m/Y', strtotime("+".$attrs['month_num']." month", strtotime($attrs['start_time'])));
                $shop['num'] = $num;
                $shop['editLink'] = $this->url->action('memberEditShop', array('id' => $shop['id'], 'name' => String::seo($shop['name'])));
                $this->tpl->insert_loop('main.shop', 'shop', $shop);
                $num++;
            }
            return $this->view();
        }
    }

    /**
     * Chi tiết gian hàng
     */
    public function detailShopAction()
    {
        $this->loadModule(array('ECShopCP', 'ECProductCP'));
        if(!empty($this->params['shop']))
        {
            $modelShop = new Models_ECShop();
            $shop = $modelShop->db->select('ec_shop.*,ec_shop_type.name as type,member.username,member.fullname')
                ->join('ec_shop_type', 'ec_shop.type_id = ec_shop_type.id')
                ->join('member', 'member.id = ec_shop.member_id', 'left')
                ->where('ec_shop.id', $this->params['shop'])
                ->getcFields();

            if(!isset($_SESSION["shop_view"][$this->params['shop']])){
                if($modelShop->db->where('id', $this->params['shop'])->update(array('view'=>($shop["view"] + 1))))
                    $_SESSION["shop_view"][$this->params['shop']] = $this->params['shop'];
            }

            $shop['content_attr'] = unserialize($shop['content_attr']);
            $shop['attr'] = Models_ECShopAttribute::getArrayCodeAttr($shop['content_attr']);
            //Get shop's product
            $model = new Models_ECShopProduct();
            $products = $model->db->select('ec_products.*')
                ->join('ec_products', 'ec_products.id=ec_shop_product.product_id')
                ->where('ec_shop_product.shop_id', $shop['id'])
                ->orderby('order_no')->getcFieldsArray();
            $i = 1;
            foreach($products as $p)
            {
                $attr = unserialize($p['content_attr']);
                $attr = Models_ECProductAttribute::getArrayCodeAttr($attr);
                if(empty($attr['price']))
                    $p['price_str'] = 'Liên hệ';
                else
                    $p['price_str'] = number_format($attr['price'], 0, ",", ".").'đ';
                $p['attr'] = $attr;
                $p['link'] = ECProductHelper::getProductLink($p);
                $p['class'] = 'mr5';
                if($i % 3 == 0)
                {
                    $p['class'] = '';
                    $this->tpl->parse('main.product.clearDiv');
                }
                $this->tpl->insert_loop('main.product', 'product', $p);
                $i++;
            }
            $shop['totalProduct'] = $i - 1;
            if(empty($shop['fullname']))
                $shop['fullname'] = $shop['username'];
            $this->tpl->assign('shop', $shop);
        }
        return $this->view();
    }

    /**
     * Thành viên tạo gian hàng
     */
    public function memberCreateShopAction()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECProductCP', 'ListCP', 'ECShopCP'));
            $months = Config::getConfig('Shop:month');
            //Get list shop type
            $model = new Models_ECShopType();
            $types = $model->db->select('id,name')->orderby('name')->getcFieldsArray();
            foreach($types as $v)
            {
                $this->tpl->insert_loop('main.type', 'type', $v);
            }
            //get list province
            $provinces = Models_ListProvince::getTreeViewAttr();
            foreach($provinces as $v)
            {
                $this->tpl->insert_loop('main.province', 'province', $v);
            }

            foreach($months as $k => $v)
            {
                $arr = array('id' => $k, 'title' => $v);
                $this->tpl->insert_loop('main.month', 'item', $arr);
            }
            $this->tpl->assign('submitText', 'Đăng ký');
            $this->tpl->assign('frmAction', $this->url->action('memberCreateShop'));
        }
        return $this->view();
    }

    public function memberCreateShopAjax()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECShopCP'));
            //Đăng gian hàng
            $this->params['member_id'] = $_SESSION['member']['id'];
            $this->params['content_attr']['start_time'] = VccDateTime::formatDate($this->params['content_attr']['start_time']);
            $this->params['created_time'] = date('Y-m-d H:i:s');
            $this->params['content_attr'] = serialize($this->params['content_attr']);
            if($this->params['image_path_upload'])
            {
                $returnPath = '/Resources/Member_Upload/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time()) . '/';
                $uploaddir = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $returnPath);
                if(!is_dir($uploaddir))
                    VccDirectory::createDir($uploaddir);
                $uploadfile = $uploaddir . basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path_upload']['name']);
                $returnPath .= basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path_upload']['name']);
                if(move_uploaded_file($this->params['image_path_upload']["tmp_name"], $uploadfile)) {
                    $image = new Image($uploadfile);
                    $info = $image->getInfo();
                    if($info[0] > 1000)
                    {
                        $image->resizeImage(1000, 0, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    elseif($info[1] > 900)
                    {
                        $image->resizeImage(0, 900, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    $this->params['image_path'] = $returnPath;
                }
            }
            unset($this->params['image_path_upload']);
            $result = Models_ECShop::createShop($this->params);
            if($result)
                return json_encode(array('success' => true, 'msg' => 'Chúc mừng bạn đã mở gian hàng thành công, hệ thống sẽ tự chuyển tới trang danh sách gian hàng của bạn trong 3 giây',
                    'url' => $this->url->action('listShopMember')));
            else
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi mở gian hàng, vui lòng thử lại sau'));
        }
        return json_encode(array('success' => false, 'msg' => 'Chức năng này chỉ dành cho thành viên'));
    }

    /**
     * Sửa gian hàng
     */
    public function memberEditShopAction()
    {
        $this->setView('memberCreateShop');
        if(isset($this->params['id']))
        {
            $months = Config::getConfig('Shop:month');
            $this->loadModule(array('ECProductCP', 'ListCP', 'ECShopCP'));
            $shop = Models_ECShop::getById($this->params['id']);
            if(!empty($shop['content_attr']))
            {
                $shop['content_attr'] = unserialize($shop['content_attr']);
                //$attr = Models_ECShopAttribute::getArrayCodeAttr($shop['content_attr']);
                //if($attr)
                //   $shop['attr'] = $attr;
                $shop['content_attr']['start_time'] = VccDateTime::userDate($shop['content_attr']['start_time']);
            }
            //Get list shop type
            $model = new Models_ECShopType();
            $types = $model->db->select('id,name')->orderby('name')->getcFieldsArray();
            foreach($types as $v)
            {
                $v['selected'] = '';
                if($v['id'] == $shop['type_id'])
                    $v['selected'] = 'selected';
                $this->tpl->insert_loop('main.type', 'type', $v);
            }
            //get list province
            $provinces = Models_ListProvince::getTreeViewAttr();
            foreach($provinces as $v)
            {
                $v['selected'] = '';
                if($v['id'] == $shop['content_attr']['province'])
                    $v['selected'] = 'selected';
                $this->tpl->insert_loop('main.province', 'province', $v);
            }

            foreach($months as $k => $v)
            {
                $arr = array('id' => $k, 'title' => $v, 'selected' => '');
                if($k == $shop['content_attr']['month_num'])
                    $arr['selected'] = 'selected';
                $this->tpl->insert_loop('main.month', 'item', $arr);
            }

            if($shop['image_path'])
            {
                $this->tpl->assign('avatar', $shop['image_path']);
                $this->tpl->parse('main.avatar');
            }
            $this->tpl->assign('submitText', 'Cập nhật');
            $this->tpl->assign('shop', $shop);
            $this->tpl->assign('frmAction', $this->url->action('memberEditShop'));
        }
        return $this->view();
    }

    public function memberEditShopAjax()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECShopCP'));
            //Đăng gian hàng
            $this->params['member_id'] = $_SESSION['member']['id'];
            $this->params['content_attr']['start_time'] = VccDateTime::formatDate($this->params['content_attr']['start_time']);
            $this->params['created_time'] = date('Y-m-d H:i:s');
            $this->params['content_attr'] = serialize($this->params['content_attr']);
            if($this->params['image_path_upload'])
            {
                $returnPath = '/Resources/Member_Upload/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time()) . '/';
                $uploaddir = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $returnPath);
                if(!is_dir($uploaddir))
                    VccDirectory::createDir($uploaddir);
                $uploadfile = $uploaddir . basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path_upload']['name']);
                $returnPath .= basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path_upload']['name']);
                if(move_uploaded_file($this->params['image_path_upload']["tmp_name"], $uploadfile)) {
                    $image = new Image($uploadfile);
                    $info = $image->getInfo();
                    if($info[0] > 1000)
                    {
                        $image->resizeImage(1000, 0, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    elseif($info[1] > 900)
                    {
                        $image->resizeImage(0, 900, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    $this->params['image_path'] = $returnPath;
                }
            }
            unset($this->params['image_path_upload']);
            $result = Models_ECShop::updateShop($this->params);
            if($result)
                return json_encode(array('success' => true, 'msg' => 'Chúc mừng bạn đã mở gian hàng thành công, hệ thống sẽ tự chuyển tới trang danh sách gian hàng của bạn trong 3 giây',
                    'url' => $this->url->action('listShopMember')));
            else
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi mở gian hàng, vui lòng thử lại sau'));
        }
        return json_encode(array('success' => false, 'msg' => 'Chức năng này chỉ dành cho thành viên'));
        return json_encode(array('success' => true));
    }

    /**
     * Xóa gian hàng
     */
    public function memberDeleteShopAjax()
    {
        if(isset($this->params['shop']))
        {
            $this->loadModule('ECShopCP');
            if(!isset($_SESSION['member']['id']))
                return json_encode(array('success' => false, 'msg' => 'Bạn cần đăng nhập trước'));
            //check gian hàng có phải của thành viên hay không
            $model = new Models_ECShop();
            $shop = $model->db->select('id,member_id')->where('id', $this->params['shop'])->getFields();
            if($shop['member_id'] == $_SESSION['member']['id'])
            {
                if($model->db->where('id', $shop['id'])->Delete())
                    return json_encode(array('success' => true, 'msg' => 'Xóa gian hàng thành công'));
                else
                    return json_encode(array('success' => false, 'msg' => 'Đã xảy ra lỗi khi xóa gian hàng'));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Bạn không có quyền xóa gian hàng này'));
        }
        return json_encode(array('success' => false, 'msg' => 'Không tìm thấy gian hàng'));
    }

    /**
     * Thành viên gửi sản phẩm
     */
    public function postProductAction()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECProductCP', 'ListCP', 'ECShopCP'));
            //Get list category
            $categories = Models_ECProductCategory::getTreeViewCat();
            foreach($categories as $v)
            {
                $this->tpl->insert_loop('main.category', 'category', $v);
            }
            //get list province
            $provinces = Models_ListProvince::getTreeViewAttr();
            foreach($provinces as $v)
            {
                $this->tpl->insert_loop('main.province', 'province', $v);
            }

            //get attribute set
            $attrSet = Models_ECSetAttribute::getListSetAttr();
            foreach($attrSet as $set)
                $this->tpl->insert_loop('main.attrSet', 'attrSet', $set);

            //Degree perfection
            $attr = Models_ECProductAttribute::getAttrByCode('degree_perfection');
            $data = AttributeCPController::getValueBox(unserialize($attr['content']));
            foreach($data as $v)
                $this->tpl->insert_loop('main.degreePerfection', 'degreePerfection', $v);

            //Get member's shop
            $shop = Models_ECShop::getShopMember(@$_SESSION['member']['id']);
            if($shop)
            {
                foreach($shop as $v)
                {
                    $this->tpl->insert_loop('main.shop.item', 'item', $v);
                }
                $this->tpl->parse('main.shop');
            }
            $this->tpl->assign("required", "required");
            $this->tpl->parse("main.btn_post");
            $this->tpl->assign('frmAction', $this->url->action('postProduct'));
        }
        return $this->view();
    }

    public function postProductAjax()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECProductCP', 'ECShopCP'));
            //Đăng gian hàng
            $this->params['member_id'] = $_SESSION['member']['id'];
            $this->params['created_time'] = date('Y-m-d H:i:s');
            if($this->params['image_path'])
            {
                $returnPath = '/Resources/Member_Upload/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time()) . '/';
                $uploaddir = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $returnPath);
                if(!is_dir($uploaddir))
                    VccDirectory::createDir($uploaddir);
                $uploadfile = $uploaddir . basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path']['name']);
                $returnPath .= basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path']['name']);
                if(move_uploaded_file($this->params['image_path']["tmp_name"], $uploadfile)) {
                    $image = new Image($uploadfile);
                    $info = $image->getInfo();
                    if($info[0] > 1000)
                    {
                        $image->resizeImage(1000, 0, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    elseif($info[1] > 900)
                    {
                        $image->resizeImage(0, 900, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    $this->params['image_path'] = $returnPath;
                }
            }
            if(isset($_SESSION['langcode']) && MULTI_LANGUAGE)
                $this->params['lang_code'] = $_SESSION['langcode'];
            $this->params['status'] = 0;
            $result = Models_ECProduct::addProduct($this->params);
            if($result !== false)
            {
                //Update product to shop
                if(!empty($this->params['shop_id']))
                    foreach($this->params['shop_id'] as $v)
                        if(!empty($v))
                            Models_ECShopProduct::addProductToShop($result, $v);

                return json_encode(array('success' => true, 'msg' => 'Chúc mừng bạn đã đăng cây thành công, hệ thống sẽ tự chuyển tới trang danh sách cây của bạn trong 3 giây',
                    'url' => $this->url->action('listProductMember')));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi đăng cây, vui lòng thử lại sau.'));
        }
        return json_encode(array('success' => false, 'msg' => 'Chức năng này chỉ dành cho thành viên'));
    }

    public function listProductMemberAction()
    {
        $this->loadModule(array('ECProductCP', 'ECShopCP'));
        $model = new Models_ECProduct();
        if($_SESSION["member"])
        {
            $listProduct = $model->db->select("id, title, image_path, content_attr")->where("member_id", $_SESSION["member"]["id"])->getcFieldsArray();
            foreach($listProduct as $k => $value)
            {
                $value["stt"] = $k+1;
                $value["attr"] = unserialize($value["content_attr"]);
                $value["attr"]["price"] = number_format($value["attr"]["price"], 0, ',', '.');
                if(!empty($value["attr"]["price"]))
                    $value["attr"]["price"] = $value["attr"]["price"]." đ";
                $value["hrefedit"] = $this->url->action("editProduct",array("id" => $value["id"]));
                $value["linkdel"] = $this->url->action("deleteProduct");
                $value["hrefdetail"] = $this->url->action("detailProductPost", array("id" => $value["id"]));
                $this->tpl->insert_loop("main.listProduct", "listProduct", $value);
            }
        }
        return $this->view();
    }

    public function editProductAction()
    {
        $this->setView("postProduct");
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECProductCP', 'ListCP', 'ECShopCP'));
            $model = new Models_ECProduct();
            $modelSProduct = new Models_ECShopProduct();
            $product = $model->db->where("id", $this->params["id"])->getcFields();
            $content_attr = unserialize($product["content_attr"]);
            $product["attr"] = $content_attr;
            $categories = Models_ECProductCategory::getTreeViewCat();
            foreach($categories as $v)
            {
                if($v["id"] == $product["category_id"])
                    $v["selected"] = "selected";
                $this->tpl->insert_loop('main.category', 'category', $v);
            }
            //get list province
            $provinces = Models_ListProvince::getTreeViewAttr();
            foreach($provinces as $v)
            {
                if($v["id"] == $content_attr["origin"])
                    $v["selected"] = "selected";
                $this->tpl->insert_loop('main.province', 'province', $v);
            }

            //get attribute set
            $attrSet = Models_ECSetAttribute::getListSetAttr();
            foreach($attrSet as $set)
            {
                if($set["id"] == $product["attr_id"])
                    $set["selected"] = "selected";
                $this->tpl->insert_loop('main.attrSet', 'attrSet', $set);
            }

            //Degree perfection
            $attr = Models_ECProductAttribute::getAttrByCode('degree_perfection');
            $data = AttributeCPController::getValueBox(unserialize($attr['content']));
            foreach($data as $v)
            {
                if($v["id"] == $content_attr["degree_perfection"])
                    $v["selected"] = "selected";
                $this->tpl->insert_loop('main.degreePerfection', 'degreePerfection', $v);
            }

            //Get member's shop
            $shop = Models_ECShop::getShopMember(@$_SESSION['member']['id']);
            $listSProduct = $modelSProduct->db->select("shop_id")->where("product_id", $product["id"])->getcFieldArray();
            if($shop)
            {
                foreach($shop as $v)
                {
                    if(!empty($listSProduct))
                        if(in_array($v["id"], $listSProduct))
                            $v["checked"] = "checked";
                    $this->tpl->insert_loop('main.shop.item', 'item', $v);
                }
                $this->tpl->parse('main.shop');
            }
            $this->tpl->assign("product", $product);
            $this->tpl->assign('frmAction', $this->url->action('editProduct'));
        }
        $this->tpl->parse("main.btn_edit");
        return $this->view();
    }

    public function editProductAjax()
    {
        if(isset($_SESSION['member']['id']))
        {
            $this->loadModule(array('ECProductCP', 'ECShopCP'));
            //Đăng gian hàng
            $this->params['member_id'] = $_SESSION['member']['id'];
            $this->params['created_time'] = date('Y-m-d H:i:s');
            if($this->params['image_path']["error"] == 0)
            {
                $returnPath = '/Resources/Member_Upload/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time()) . '/';
                $uploaddir = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $returnPath);
                if(!is_dir($uploaddir))
                    VccDirectory::createDir($uploaddir);
                $uploadfile = $uploaddir . basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path']['name']);
                $returnPath .= basename(date('his')."_".$_SESSION['member']['id']."_".$this->params['image_path']['name']);
                if(move_uploaded_file($this->params['image_path']["tmp_name"], $uploadfile)) {
                    $image = new Image($uploadfile);
                    $info = $image->getInfo();
                    if($info[0] > 1000)
                    {
                        $image->resizeImage(1000, 0, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    elseif($info[1] > 900)
                    {
                        $image->resizeImage(0, 900, 'auto');
                        $image->save($uploadfile, null, false);
                    }
                    $this->params['image_path'] = $returnPath;
                }
            }
            else
                unset($this->params["image_path"]);

            if(isset($_SESSION['langcode']) && MULTI_LANGUAGE)
                $this->params['lang_code'] = $_SESSION['langcode'];
            $this->params['status'] = 0;
            $result = Models_ECProduct::updateProduct($this->params);
            if($result !== false)
            {
                Models_ECShopProduct::deleteProduct($result);
                //Update product to shop
                if(!empty($this->params['shop_id']))
                    foreach($this->params['shop_id'] as $v)
                        if(!empty($v))
                            Models_ECShopProduct::addProductToShop($result, $v);

                return json_encode(array('success' => true, 'msg' => 'Chúc mừng bạn đã đăng cây thành công, hệ thống sẽ tự chuyển tới trang danh sách cây của bạn trong 3 giây',
                    'url' => $this->url->action('listProductMember')));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi đăng cây, vui lòng thử lại sau.'));
        }
        return json_encode(array('success' => false, 'msg' => 'Chức năng này chỉ dành cho thành viên'));
    }

    public function deleteProductAjax()
    {
        $this->loadModule(array('ECProductCP', 'ECShopCP'));
        if(!empty($this->params["id"]))
        {
            $id=$this->params["id"];
            $model = new Models_ECProduct();
            if($model->Delete("id = $id"))
            {
                Models_ECShopProduct::deleteProduct($id);
                Models_ECProductAttrs::deleteByProduct($id);
                return json_encode(array("success" => true,"msg" => "TC", "url" => $this->url->action("listProductMember")));
            }
            else
                return json_encode(array("success" => false, "msg" => $model->error));
        }
    }

    public function detailProductPostAction()
    {
        $this->loadModule(array('ECProductCP', 'ECShopCP', 'ListCP'));;
        if($_SESSION["member"])
        {
            $this->loadModule(array('ECProductCP', 'ListCP', 'ECShopCP'));
            $model = new Models_ECProduct();
            $modelSProduct = new Models_ECShopProduct();
            $product = $model->db->where("id", $this->params["id"])->getcFields();
            $product["attr"] = unserialize($product["content_attr"]);
            $product["price_str"] = number_format($product["attr"]["price"], 0, ",", ".");
            if(!empty($product["price_str"]))
                $product["price_str"].= " đ";
            $product["category"] = Models_ECProductCategory::getCategory($product["category_id"]);
            $modelProvince = new Models_ListProvince();
            $product["origin"] = $modelProvince->db->select("name")->where("id", $product["attr"]["origin"])->getcField();
            $modelAttr = new Models_ECSetAttribute();
            $product["attr_groups"] = $modelAttr->getAttribute($product["attr_id"]);
            $listSProduct = $modelSProduct->db->select("ec_shop.name")->join("ec_shop", "ec_shop.id = ec_shop_product.shop_id")
                            ->where("ec_shop_product.product_id", $product["id"])->getcFieldArray();
            if(!empty($listSProduct))
                $product["shop_name"] = implode(",", $listSProduct);
            $this->tpl->assign("product", $product);
        }
        return $this->view();
    }
}