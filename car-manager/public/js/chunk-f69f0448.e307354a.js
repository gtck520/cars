(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-f69f0448"],{"5e5e":function(t,e,a){"use strict";a.r(e);var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"app-container"},[a("el-form",{staticClass:"demo-form-inline",attrs:{inline:!0,model:t.form}},[a("el-form-item",{attrs:{label:"所属城市:"}},[a("el-select",{staticClass:"filter-item",staticStyle:{width:"90px"},attrs:{placeholder:"省份",clearable:""},on:{change:function(e){return t.getCityCity(t.form.province)}},model:{value:t.form.province,callback:function(e){t.$set(t.form,"province",e)},expression:"form.province"}},[a("el-option",{key:"0",attrs:{label:"请选择省份",value:"0"}}),t._l(t.province,(function(t){return a("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})}))],2),a("el-select",{staticClass:"filter-item",staticStyle:{width:"130px"},attrs:{placeholder:"城市",clearable:""},on:{change:function(e){return t.getCityArea(t.form.city)}},model:{value:t.form.city,callback:function(e){t.$set(t.form,"city",e)},expression:"form.city"}},t._l(t.city,(function(t){return a("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})})),1),a("el-select",{staticClass:"filter-item",staticStyle:{width:"140px"},attrs:{placeholder:"区县",clearable:""},model:{value:t.form.area,callback:function(e){t.$set(t.form,"area",e)},expression:"form.area"}},t._l(t.area,(function(t){return a("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})})),1)],1),a("el-form-item",{attrs:{label:"发布人手机号:"}},[a("el-input",{model:{value:t.form.mobile,callback:function(e){t.$set(t.form,"mobile",e)},expression:"form.mobile"}})],1),a("el-form-item",{attrs:{label:"关键字搜索:"}},[a("el-input",{model:{value:t.form.search,callback:function(e){t.$set(t.form,"search",e)},expression:"form.search"}})],1),a("el-form-item",[a("el-button",{attrs:{icon:"el-icon-search",type:"primary"},on:{click:function(e){return t.search(1)}}},[t._v("查询")])],1)],1),a("el-table",{staticStyle:{width:"100%","margin-bottom":"20px"},attrs:{center:"",data:t.cars,"row-key":"id"}},[a("el-table-column",{attrs:{prop:"id",width:"50",label:"ID"}}),a("el-table-column",{attrs:{label:"用户信息",width:"180"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("div",[a("p",[t._v("姓名："+t._s(e.row.realname))]),a("p",[t._v("电话:"+t._s(e.row.mobile))])])]}}])}),a("el-table-column",{attrs:{label:"车架号"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("div",[a("p",[t._v(t._s(e.row.chejiahao))])])]}}])}),a("el-table-column",{attrs:{prop:"city_name",label:"所属城市"}}),a("el-table-column",{attrs:{prop:"title",label:"车辆信息"}}),a("el-table-column",{attrs:{prop:"price",label:"价格",width:"100"}}),a("el-table-column",{attrs:{prop:"create_time",label:"发布时间"}}),a("el-table-column",{attrs:{label:"状态"},scopedSlots:t._u([{key:"default",fn:function(e){return[1==e.row.status?a("span",[t._v("审核通过")]):0==e.row.status?a("span",[t._v("未审核")]):a("span",[t._v("不通过")])]}}])}),a("el-table-column",{attrs:{label:"上下架"},scopedSlots:t._u([{key:"default",fn:function(e){return[1==e.row.is_hidden?a("span",[t._v("下架")]):a("span",[t._v("上架")])]}}])}),a("el-table-column",{attrs:{label:"操作",width:"300"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(a){return t.detail(e.row)}}},[t._v("查看")]),1==e.row.status?a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(a){return t.updateStatus(e.row,0)}}},[t._v("不通过")]):a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(a){return t.updateStatus(e.row,1)}}},[t._v("通过")]),1==e.row.is_hidden?a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(a){return t.updateHidden(e.row,0)}}},[t._v("上架")]):a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:function(a){return t.updateHidden(e.row,1)}}},[t._v("下架")])]}}])})],1),t.cars.length>0?a("el-pagination",{attrs:{layout:"prev, pager, next","current-page":t.form.p,total:t.total,background:""},on:{"current-change":t.search}}):t._e(),a("el-dialog",{staticStyle:{"line-height":"2"},attrs:{title:"动态详情",visible:t.showDialog,center:"",width:"30%"},on:{"update:visible":function(e){t.showDialog=e}}},[a("p",[t._v("id:"+t._s(t.topicDeail.id))]),a("p",[t._v("昵称:"+t._s(t.topicDeail.nickname))]),a("p",[t._v("电话:"+t._s(t.topicDeail.mobile))]),a("p",[t._v("动态内容:"+t._s(t.topicDeail.content))])])],1)},i=[],r=a("7274"),c=a("66b4"),o={data:function(){return{showDialog:!1,formLabelWidth:"120px",province:[],city:[],area:[],form:{province_id:"",city_id:"",area_id:"",p:1,c:10,search:"",mobile:""},cars:[],total:0,topicDeail:""}},created:function(){this.getCity(),this.getCars()},methods:{search:function(t){this.form.p=t,this.getCars()},getCity:function(){var t=this;r["a"].getCity().then((function(e){t.province=e}))},getCityCity:function(t){var e=this;this.cityname.city="",this.cityname.area="",r["a"].getCity({pid:t}).then((function(t){e.city=t}))},getCityArea:function(t){var e=this;this.cityname.area="",r["a"].getCity({pid:t}).then((function(t){e.area=t}))},getCars:function(){var t=this;c["a"].cars(this.form).then((function(e){t.cars=e.rs,t.total=e.total}))},detail:function(t){this.$router.push({path:"detail/"+t.id})},updateStatus:function(t,e){var a=this;this.$confirm("是否要"+(0==e?"拒绝通过":"审核通过")+"该车辆?",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then((function(){c["a"].updateStatus(t.id,{status:e}).then((function(){a.getCars()}))})).catch((function(){a.$message({type:"info",message:"已取消该操作"})}))},updateHidden:function(t,e){var a=this;this.$confirm("是否要"+(0==e?"下架":"上架")+"该车辆?",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then((function(){c["a"].updateStatus(t.id,{is_hidden:e}).then((function(){a.getCars()}))})).catch((function(){a.$message({type:"info",message:"已取消该操作"})}))}}},l=o,s=a("2877"),u=Object(s["a"])(l,n,i,!1,null,"79ae0729",null);e["default"]=u.exports},"66b4":function(t,e,a){"use strict";var n=a("a27e");e["a"]={cars:function(t){return Object(n["a"])("/cars",t)},carDetail:function(t,e){return Object(n["a"])("/car/".concat(t),e)},update:function(t,e){return Object(n["c"])("/car/".concat(t),e)},remove:function(t){return Object(n["d"])("/car/".concat(t))},updateStatus:function(t,e){return Object(n["c"])("/car/updateStatus/".concat(t),e)}}},7274:function(t,e,a){"use strict";var n=a("a27e");e["a"]={getCity:function(t){return Object(n["b"])("/cityarea",t)},getActive:function(t){return Object(n["a"])("/cityactives",t)},add:function(t){return Object(n["b"])("/cityactive",t)},update:function(t,e){return Object(n["c"])("/cityactive/".concat(t),e)},remove:function(t){return Object(n["d"])("/cityactive/".concat(t))},updateStatus:function(t,e){return Object(n["c"])("/cityactive/".concat(t),e)},getLevel:function(t){return Object(n["a"])("/levels",t)},updateLevel:function(t,e){return Object(n["c"])("/level/".concat(t),e)}}}}]);