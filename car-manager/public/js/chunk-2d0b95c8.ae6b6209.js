(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-2d0b95c8"],{"332d":function(e,t,l){"use strict";l.r(t);var a=function(){var e=this,t=e.$createElement,l=e._self._c||t;return l("div",{staticClass:"app-container"},[l("el-form",{staticClass:"demo-form-inline",attrs:{inline:!0,model:e.formInline}},[l("el-form-item",{attrs:{label:"用户id："}},[l("el-input",{attrs:{placeholder:"请输入用户名"},model:{value:e.formInline.user,callback:function(t){e.$set(e.formInline,"user",t)},expression:"formInline.user"}})],1),l("el-form-item",{attrs:{label:"类型："}},[l("el-select",{attrs:{placeholder:"活动区域"},model:{value:e.formInline.region,callback:function(t){e.$set(e.formInline,"region",t)},expression:"formInline.region"}},[l("el-option",{attrs:{label:"提现",value:"shanghai"}}),l("el-option",{attrs:{label:"充值",value:"beijing"}}),l("el-option",{attrs:{label:"打赏",value:"beijing"}}),l("el-option",{attrs:{label:"被打赏",value:"beijing"}}),l("el-option",{attrs:{label:"见面红包",value:"beijing"}})],1)],1),l("el-form-item",[l("span",{staticClass:"demonstration"},[e._v("创建时间：")]),l("el-date-picker",{attrs:{type:"date",placeholder:"选择日期"}})],1),l("el-form-item",[l("el-button",{attrs:{type:"primary"},on:{click:e.onSubmit}},[e._v("搜索")])],1)],1),l("div",[l("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData}},[l("el-table-column",{attrs:{prop:"date",label:"ID",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"会员信息",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"姓名电话",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"类型",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"金额（元）",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"零钱余额（元）",width:"180"}}),l("el-table-column",{attrs:{prop:"name",label:"创建时间"}})],1)],1)],1)},n=[],o={data:function(){return{formInline:{user:"",region:""},tableData:[{date:"2016-05-02",name:"王小虎",address:"上海市普陀区金沙江路 1518 弄"},{date:"2016-05-04",name:"王小虎",address:"上海市普陀区金沙江路 1517 弄"},{date:"2016-05-01",name:"王小虎",address:"上海市普陀区金沙江路 1519 弄"},{date:"2016-05-03",name:"王小虎",address:"上海市普陀区金沙江路 1516 弄"}]}},methods:{onSubmit:function(){console.log("submit!")}}},r=o,i=l("2877"),s=Object(i["a"])(r,a,n,!1,null,null,null);t["default"]=s.exports}}]);