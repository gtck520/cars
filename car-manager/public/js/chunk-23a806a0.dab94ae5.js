(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-23a806a0"],{"5f44":function(t,e,n){"use strict";n.r(e);var c=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"app-container"},[n("el-form",{staticClass:"demo-form-inline",attrs:{inline:!0,model:t.form}},[n("el-select",{staticClass:"filter-item",staticStyle:{width:"90px"},attrs:{placeholder:"省份",clearable:""},on:{change:function(e){return t.getCityCity(t.cityname.province)}},model:{value:t.cityname.province,callback:function(e){t.$set(t.cityname,"province",e)},expression:"cityname.province"}},[n("el-option",{key:"0",attrs:{label:"请选择省份",value:"0"}}),t._l(t.province,(function(t){return n("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})}))],2),n("el-select",{staticClass:"filter-item",staticStyle:{width:"130px"},attrs:{placeholder:"城市",clearable:""},on:{change:function(e){return t.getCityArea(t.cityname.city)}},model:{value:t.cityname.city,callback:function(e){t.$set(t.cityname,"city",e)},expression:"cityname.city"}},t._l(t.city,(function(t){return n("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})})),1),n("el-select",{staticClass:"filter-item",staticStyle:{width:"140px"},attrs:{placeholder:"区县",clearable:""},model:{value:t.cityname.area,callback:function(e){t.$set(t.cityname,"area",e)},expression:"cityname.area"}},t._l(t.area,(function(t){return n("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})})),1),n("el-form-item",[n("el-input",{attrs:{placeholder:"手机号"},model:{value:t.form.mobile,callback:function(e){t.$set(t.form,"mobile",e)},expression:"form.mobile"}})],1),n("el-form-item",[n("el-button",{attrs:{icon:"el-icon-search",type:"primary"},on:{click:function(e){return t.search(1)}}},[t._v("查询")])],1)],1),n("el-table",{staticStyle:{width:"100%","margin-bottom":"20px"},attrs:{border:"",data:t.users,"row-key":"id"}},[n("el-table-column",{attrs:{width:"180",label:"ID"},scopedSlots:t._u([{key:"default",fn:function(e){return[n("div",{staticStyle:{"text-align":"center"}},[n("el-avatar",{attrs:{src:e.row.avatar}}),n("br"),t._v("\n            "+t._s(e.row.user_id)+"\n          ")],1)]}}])}),n("el-table-column",{attrs:{label:"会员信息"},scopedSlots:t._u([{key:"default",fn:function(e){return[n("div",[n("p",[t._v("昵称："+t._s(e.row.nickname))]),n("p",[t._v("手机："+t._s(e.row.mobile))]),n("p",[t._v("性别："+t._s(e.row.gender))]),n("p",[t._v("真名："+t._s(e.row.realname))]),n("p",[t._v("签名："+t._s(e.row.sign))])])]}}])}),n("el-table-column",{attrs:{label:"城市"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(t._s(e.row.city_fullname)+"\n        ")]}}])}),n("el-table-column",{attrs:{label:"登录信息"},scopedSlots:t._u([{key:"default",fn:function(e){return[n("div",[n("p",[t._v("注册时间："+t._s(e.row.register_time))]),n("p",[t._v("最后在线："+t._s(e.row.last_online_time))]),n("p",[t._v("登录ip："+t._s(e.row.last_login_ip))]),n("p",[t._v("客户端："+t._s(e.row.client))])])]}}])}),n("el-table-column",{attrs:{label:"操作",width:"180"},scopedSlots:t._u([{key:"default",fn:function(e){return[n("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(n){return t.goDetail(e.row)}}},[t._v("会员详情")])]}}])})],1),t.users.length>0?n("el-pagination",{attrs:{layout:"prev, pager, next","current-page":t.form.p,total:t.total,background:""},on:{"current-change":t.search}}):t._e()],1)},a=[],r=n("8ca5"),i=n("7274"),o={data:function(){return{formLabelWidth:"120px",form:{city_id:"",mobile:"",user_id:"",p:1,c:10},users:[],total:0,tagType:["success","info","warning","danger"],cityname:{province:"",city:"",area:""},province:[],city:[],area:[]}},created:function(){this.getUser(),this.getCity()},methods:{randomType:function(t){return t[Math.ceil(4*Math.random())]},search:function(t){this.form.p=t,this.getUser()},goDetail:function(t){this.$router.push({path:"detail/"+t.id})},getUser:function(){var t=this;r["a"].users(this.form).then((function(e){t.users=e.rs||[],t.total=e.total||0}))},getCity:function(){var t=this;i["a"].getCity().then((function(e){t.province=e}))},getCityCity:function(t){var e=this;this.cityname.city="",this.cityname.area="",i["a"].getCity({pid:t}).then((function(t){e.city=t}))},getCityArea:function(t){var e=this;this.cityname.area="",i["a"].getCity({pid:t}).then((function(t){e.area=t}))}}},s=o,l=n("2877"),u=Object(l["a"])(s,c,a,!1,null,"68e6ac5f",null);e["default"]=u.exports},7274:function(t,e,n){"use strict";var c=n("a27e");e["a"]={getCity:function(t){return Object(c["b"])("/cityarea",t)},getActive:function(t){return Object(c["a"])("/cityactives",t)},add:function(t){return Object(c["b"])("/cityactive",t)},update:function(t,e){return Object(c["c"])("/cityactive/".concat(t),e)},remove:function(t){return Object(c["d"])("/cityactive/".concat(t))},updateStatus:function(t,e){return Object(c["c"])("/cityactive/".concat(t),e)},getLevel:function(t){return Object(c["a"])("/levels",t)},updateLevel:function(t,e){return Object(c["c"])("/level/".concat(t),e)}}},"8ca5":function(t,e,n){"use strict";var c=n("a27e");e["a"]={users:function(t){return Object(c["a"])("/users",t)},info:function(t){return Object(c["a"])("/info",t)},update:function(t,e){return Object(c["c"])("/Purpose/".concat(t),e)},remove:function(t){return Object(c["d"])("/Purpose/".concat(t))},updateStatus:function(t,e){return Object(c["c"])("/Purpose/".concat(t,"/status"),e)},getSysscore:function(t){return Object(c["a"])("/sysscore",t)},addSysscore:function(t,e){return Object(c["b"])("/sysscore/".concat(t),e)},updateSysscore:function(t,e){return Object(c["c"])("/sysscore/".concat(t),e)},removeSysscore:function(t){return Object(c["d"])("/sysscore/".concat(t))},getUserScore:function(t,e){return Object(c["a"])("/user/".concat(t,"/score_records"),e)},getSysScore:function(){return Object(c["a"])("/getSysScore")}}}}]);