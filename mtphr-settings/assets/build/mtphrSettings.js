(()=>{"use strict";const e=window.wp.i18n,t=window.wp.element,s=window.wp.components,i={},n=(e,t)=>{i[e]=t},l=window.ReactJSXRuntime,a=({field:e,value:t,onChange:n,settings:a,settingsId:r})=>{const{container:o}=e,{isBorderless:c,padding:d}=o||{},p=(u=e.type,i[u]);var u;return p?(0,l.jsxs)(s.Card,{className:`mtphrSettings__field mtphrSettings__field--${e.type} ${e.class||""}`,isRounded:!1,size:"small",isBorderless:c,children:["group"===e.type&&e.label&&(0,l.jsx)(s.CardHeader,{className:"$mtphrSettings__field__heading",children:(0,l.jsx)(s.__experimentalHeading,{level:4,children:e.label})}),(0,l.jsx)(s.CardBody,{className:"mtphrSettings__field__input-wrapper",style:{padding:d},children:(0,l.jsx)(p,{field:e,value:t,onChange:n,settings:a,settingsId:r})})]}):(console.error(`No component registered for field type '${e.type}'`),(0,l.jsxs)(s.Notice,{status:"error",isDismissible:!1,children:["Unknown field type: ",e.type]}))},r=({settingsId:i})=>{const n=window[`${i}Vars`],[r,o]=(0,t.useState)(n.settings),[c,d]=(0,t.useState)(!1),[p,u]=(0,t.useState)(null),h=n.field_sections,g=n.fields,{Fill:m,Slot:x}=(0,s.createSlotFill)(`${i}Notices`),f=()=>p&&(0,l.jsx)(m,{children:(0,l.jsx)(s.Notice,{status:p.status,onRemove:()=>u(null),isDismissible:!0,children:p.message})}),v=h.reduce(((e,t)=>(e[t.id]=t,e)),{}),w=g.reduce(((e,t)=>{const s=t.section||"general",i=v[s];if(!i)return e;let n=e.find((e=>e.id===s));return n||(n={id:s,slug:i.slug,label:i.label,order:void 0!==i.order?i.order:10,isIntegration:void 0!==i.is_integration&&i.is_integration,fields:[]},e.push(n)),n.fields.push(t),e}),[]);w.sort(((e,t)=>e.order-t.order));const b=w.filter((e=>"general"===e.id||"advanced"===e.id||!e.isIntegration||r.integrations?.includes(e.id))),j=b.map((e=>({id:e.id,name:e.slug,title:e.label}))),_=new URLSearchParams(window.location.search),y=_.get("section")?_.get("section"):w[0].slug,C=j.map((e=>e.name)).includes(y)?y:"general",[S,P]=(0,t.useState)(C);return(0,t.useEffect)((()=>{const e=new URLSearchParams(window.location.search);"general"===S?e.delete("section"):e.set("section",S);const t=`${window.location.pathname}?${e.toString()}`;window.history.replaceState(null,"",t)}),[S]),(0,t.useEffect)((()=>{const e=r.integrations||[],t=["general","advanced"];document.querySelectorAll(".wp-submenu a[href*='edit.php?post_type=mtphr_email_template&page=settings&section=']").forEach((s=>{const i=s.getAttribute("href"),n=new URLSearchParams(i.split("?")[1]).get("section");t.includes(n)||e.includes(n)?s.closest("li").style.display="":s.closest("li").style.display="none"}))}),[r]),(0,l.jsx)(s.SlotFillProvider,{children:(0,l.jsxs)(s.Card,{className:`mtphrSettings ${i}`,children:[(0,l.jsx)(s.CardHeader,{children:(0,l.jsx)(s.__experimentalHeading,{level:1,children:(0,e.__)("Settings","mtphr-emailcustomizer")})}),(0,l.jsx)("div",{className:"toolbar",children:(0,l.jsx)(x,{})}),(0,l.jsx)(s.CardBody,{className:"mtphrSettings__form",children:(0,l.jsx)(s.TabPanel,{className:"mtphrSettings__tabs",activeClass:"is-active",tabs:j,initialTabName:S,onSelect:e=>{P(e)},children:e=>{const t=b.find((t=>t.id===e.id));return(0,l.jsx)("div",{className:"mtphrSettings__section",children:t.fields.map((e=>(0,l.jsx)(a,{field:e,value:r[e.id]||"",onChange:(t,s,i)=>{"group"===e.type?((e,t,s)=>{const{id:i,value:n}=e;o((e=>{const i=Array.isArray(e[t])?[...e[t]]:[];return i[s]=n,{...e,[t]:i}}))})(t,s,i):(e=>{const{id:t,value:s}=e;o((e=>({...e,[t]:s})))})(t)},settings:r,settingsId:i},e.id)))})}})}),(0,l.jsx)(s.CardFooter,{className:"mtphrSettings__footer",children:(0,l.jsx)(s.Button,{onClick:()=>{d(!0),fetch(`${n.restUrl}settings`,{method:"POST",headers:{"X-WP-Nonce":n.nonce,"Content-Type":"application/json"},body:JSON.stringify(r)}).then((e=>e.ok?e.json():e.json().then((t=>{const s=t?.message||`HTTP Error ${e.status}`;throw new Error(s)})).catch((()=>{throw new Error(`HTTP Error ${e.status}`)})))).then((t=>{o(t),d(!1),u({status:"success",message:(0,e.__)("Settings saved successfully!","mtphr-emailcustomizer")})})).catch((t=>{d(!1),u({status:"error",message:`${(0,e.__)("Error saving settings.","mtphr-emailcustomizer")} ${t.message}`})}))},disabled:c,variant:"primary",isBusy:c,children:c?"Saving...":"Save Settings"})}),(0,l.jsx)(f,{})]})})},{useState:o}=wp.element,c=({field:e,settings:t,settingsId:i})=>{const[n,a]=o(!1),[r,c]=o(null),{action:d,class:p,description:u,disabled:h,icon:g,iconPosition:m,iconSize:x,isDestructive:f,isLink:v,size:w,text:b,target:j,variant:_="secondary"}=e,{Fill:y}=(0,s.createSlotFill)(`${i}Notices`),C=()=>r&&(0,l.jsx)(y,{children:(0,l.jsx)(s.Notice,{status:r.status,onRemove:()=>c(null),isDismissible:!0,children:(0,l.jsx)("div",{dangerouslySetInnerHTML:{__html:r.message}})})}),{baseControlProps:S}=(0,s.useBaseControlProps)(e);return(0,l.jsxs)(s.BaseControl,{...S,__nextHasNoMarginBottom:!0,children:[(0,l.jsx)(s.Button,{className:p,description:u,disabled:n&&"api"===d?.type,href:"api"===d?.type?null:d.url,icon:g,iconPosition:m,iconSize:x,isBusy:n,isDestructive:f,isLink:v,onClick:()=>{if(d?.confirm){const e=d.confirm;if(!window.confirm(e))return}if(d&&"api"===d.type){const e=d.url,s=window[`${i}Vars`];c(null),a(!0),fetch(e,{method:"POST",headers:{"X-WP-Nonce":s.nonce,"Content-Type":"application/json"},body:JSON.stringify(t)}).then((e=>e.json())).then((e=>{a(!1),d.response&&c("object"==typeof e&&null!==e?e:{status:"success",message:e})})).catch((e=>{a(!1),console.error("Error:",e)}))}else d&&"default"!==d.type||(window.location.href=d.url)},size:w,target:j,text:b,variant:_}),(0,l.jsx)(C,{})]})},d=window.React,p=(0,t.forwardRef)((function({icon:e,size:s=24,...i},n){return(0,t.cloneElement)(e,{width:s,height:s,...i,ref:n})})),u=window.wp.primitives,h=(0,l.jsx)(u.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",children:(0,l.jsx)(u.Path,{d:"M15.1 4.8l-3-2.5V4c-4.4 0-8 3.6-8 8 0 3.7 2.5 6.9 6 7.7.3.1.6.1 1 .2l.2-1.5c-.4 0-.7-.1-1.1-.2l-.1.2v-.2c-2.6-.8-4.5-3.3-4.5-6.2 0-3.6 2.9-6.5 6.5-6.5v1.8l3-2.5zM20 11c-.2-1.4-.7-2.7-1.6-3.8l-1.2.8c.7.9 1.1 2 1.3 3.1L20 11zm-1.5 1.8c-.1.5-.2 1.1-.4 1.6s-.5 1-.8 1.5l1.2.9c.4-.5.8-1.1 1-1.8s.5-1.3.5-2l-1.5-.2zm-5.6 5.6l.2 1.5c1.4-.2 2.7-.7 3.8-1.6l-.9-1.1c-.9.7-2 1.1-3.1 1.2z"})}),g=(0,l.jsx)(u.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",children:(0,l.jsx)(u.Path,{d:"M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"})}),m=(0,l.jsx)(u.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",children:(0,l.jsx)(u.Path,{d:"M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"})}),{__}=wp.i18n,{useState:x}=wp.element;n("button",c),n("buttons",(({field:e,settings:t,settingsId:i})=>{const{alignment:n,direction:a,justify:r,spacing:o,wrap:d,class:p="",buttons:u}=e,{baseControlProps:h}=(0,s.useBaseControlProps)(e);return(0,l.jsx)(s.BaseControl,{...h,__nextHasNoMarginBottom:!0,children:(0,l.jsx)(s.__experimentalHStack,{alignment:n,direction:a,justify:r,spacing:o,wrap:d,className:p,children:u.map(((e,s)=>(0,l.jsx)(c,{field:e,settings:t,settingsId:i},e.id||s)))})})})),n("color",(({field:e,value:t=[],onChange:i})=>{const[n,a]=(0,d.useState)(null),{class:r,min:o=1,max:c,id:p}=e,{baseControlProps:u,controlProps:h}=(0,s.useBaseControlProps)(e);return(0,l.jsxs)(s.BaseControl,{...u,children:[(0,l.jsxs)("div",{style:{display:"flex",gap:"8px",flexWrap:"wrap"},children:[t.map(((e,s)=>(0,l.jsxs)("div",{style:{position:"relative"},children:[(0,l.jsx)("div",{onClick:()=>{a(n===s?null:s)},style:{width:"32px",height:"32px",backgroundColor:e,border:n===s?"2px solid blue":"1px solid gray",borderRadius:"50%",cursor:"pointer"},title:`Color ${s+1}`}),s>=o&&(0,l.jsx)("button",{onClick:()=>(e=>{const s=t.filter(((t,s)=>s!==e));i({id:p,value:s}),n===e?a(null):n>e&&a(n-1)})(s),style:{position:"absolute",top:"-4px",right:"-4px",backgroundColor:"white",border:"1px solid gray",borderRadius:"50%",width:"16px",height:"16px",cursor:"pointer",display:"flex",alignItems:"center",justifyContent:"center",padding:0},"aria-label":`Remove color ${s+1}`,children:"×"})]},s))),t.length<c&&(0,l.jsx)("button",{onClick:()=>{const e=[...t,"#000000"];i({id:p,value:e}),a(e.length-1)},style:{width:"32px",height:"32px",backgroundColor:"#f0f0f0",border:"1px dashed gray",borderRadius:"50%",cursor:"pointer",display:"flex",alignItems:"center",justifyContent:"center"},"aria-label":"Add color",children:"+"})]}),null!==n&&(0,l.jsx)(s.ColorPalette,{value:(()=>{if(null!=n)return t[n]})(),onChange:e=>{const s=[...t];s[n]=e,i({id:p,value:s})},asButtons:!0,style:{marginTop:"16px"}})]})})),n("checkboxes",(({field:e,value:t=[],onChange:i})=>{const{class:n,disabled:a,help:r,label:o,id:c,choices:d}=e,{baseControlProps:p,controlProps:u}=(0,s.useBaseControlProps)(e);return(0,l.jsx)(s.BaseControl,{...p,children:(0,l.jsx)("fieldset",{children:Object.entries(d).map((([e,n])=>(0,l.jsx)(s.CheckboxControl,{label:n,checked:t.includes(e),onChange:s=>((e,s)=>{const n=e?[...t,s]:t.filter((e=>e!==s));i({id:c,value:n})})(s,e),disabled:a},e)))})})})),n("edd_license",(({field:e,value:t,onChange:i,settingsId:n})=>{const{class:a,id:r,license_data:o={},activate_url:c,deactivate_url:d,refresh_url:u}=e,[f,v]=x(null),[w,b]=x(o),[j,_]=x(null),{Fill:y}=(0,s.createSlotFill)(`${n}Notices`),C=()=>j&&(0,l.jsx)(y,{children:(0,l.jsx)(s.Notice,{status:j.status,onRemove:()=>_(null),isDismissible:!0,children:j.message})}),S=e=>{const s=window[`${n}Vars`];let i=!1;switch(e){case"activate":i=c||!1;break;case"deactivate":i=d||!1;break;case"refresh":i=u||!1}if(!i)return!1;v(e),fetch(i,{method:"POST",headers:{"X-WP-Nonce":s.nonce,"Content-Type":"application/json"},body:JSON.stringify({license:t})}).then((e=>e.json())).then((e=>{b(e),v(!1);let t="success",s=__("License key saved successfully!","mtphr-emailcustomizer");switch(e.license){case"valid":t="success",s=__("License key has been activated!","mtphr-emailcustomizer");break;case"deactivated":t="warning",s=__("License key has been deactivated.","mtphr-emailcustomizer")}_({status:t,message:s})})).catch((e=>{v(!1),_({status:"error",message:__("Error saving license key updates.","mtphr-emailcustomizer")}),console.error("Error:",e)}))},{baseControlProps:P,controlProps:N}=(0,s.useBaseControlProps)(e);return(0,l.jsxs)(s.BaseControl,{...P,children:[(0,l.jsxs)(s.__experimentalVStack,{children:[(0,l.jsxs)(s.__experimentalHStack,{alignment:"left",children:[(0,l.jsx)(s.__experimentalText,{children:`Status: ${w.license}`}),"valid"==w.license&&(0,l.jsx)(s.__experimentalText,{children:`Expires: ${w.expires}`})]}),(0,l.jsxs)(s.__experimentalHStack,{wrap:!1,children:[(0,l.jsx)(s.__experimentalSpacer,{children:(0,l.jsx)(s.__experimentalInputControl,{style:{height:"50px"},value:(()=>{if(!t||t.length<=15)return t;const e=t.slice(0,15),s=t.length-15;return e+"*".repeat(s)})(),onChange:e=>{i({id:r,value:e})},__nextHasNoMarginBottom:!0})}),(0,l.jsxs)(s.ButtonGroup,{children:[w.license&&"valid"==w.license&&(0,l.jsx)(s.Tooltip,{text:__("Refresh License","mtphr-emailcustomizer"),children:(0,l.jsx)(s.Button,{style:{height:"50px",width:"50px"},variant:"secondary",disabled:f,isBusy:"refresh"==f,onClick:()=>S("refresh"),children:(0,l.jsx)(p,{icon:h})})}),w.license&&"valid"==w.license?(0,l.jsx)(s.Tooltip,{text:__("Deactivate License","mtphr-emailcustomizer"),children:(0,l.jsx)(s.Button,{style:{height:"50px",width:"50px"},variant:"primary",isDestructive:!0,disabled:f,isBusy:"deactivate"==f,onClick:()=>S("deactivate"),children:(0,l.jsx)(p,{icon:g})})}):(0,l.jsx)(s.Tooltip,{text:__("Activate License","mtphr-emailcustomizer"),children:(0,l.jsx)(s.Button,{style:{height:"50px",width:"50px"},variant:"primary",disabled:f,isBusy:"activate"==f,onClick:()=>S("activate"),children:(0,l.jsx)(p,{icon:m})})})]})]})]}),(0,l.jsx)(C,{})]})})),n("group",(({field:e,value:t,onChange:i,settings:n,settingsId:r})=>{const{alignment:o,direction:c,justify:d,spacing:p,wrap:u,class:h="",id:g,label:m,tooltip:x,fields:f}=e;return(0,l.jsx)(s.__experimentalHStack,{alignment:o,direction:c,justify:d,spacing:p,wrap:u,className:h,children:f.map(((e,s)=>(0,l.jsx)(a,{field:e,value:t[s]||e.default_value||"",onChange:e=>{i(e,g,s)},settings:n,settingsId:r},e.id)))})})),n("heading",(({field:e})=>{const{level:t=4,label:i}=e;return(0,l.jsx)(s.__experimentalHeading,{level:t,children:i})})),n("mapping",(({field:e,value:i={},onChange:n})=>{const{label:a,id:r,help:o,map_source:c,map_options:d,disabled:p}=e,[u,h]=(0,t.useState)((()=>c.map((e=>({tag:e.tag,label:e.label,value:i[e.tag]||""})))));return(0,l.jsx)(s.BaseControl,{label:a,help:o,id:r,children:u.map((e=>{return(0,l.jsxs)(s.__experimentalHStack,{spacing:"10px",className:"mapping-field-row",alignment:"left",children:[(0,l.jsx)("div",{className:"mapping-field-label",style:{flex:1},children:e.label}),(0,l.jsx)("div",{className:"mapping-field-select",style:{flex:2},children:(0,l.jsx)(s.SelectControl,{value:e.value,options:[{value:"",label:"-- Select --",disabled:!1},...(t=e.value,d.map((e=>({value:e.tag,label:e.label,disabled:u.some((s=>s.value===e.tag&&s.value!==t))}))))],onChange:t=>((e,t)=>{const s=u.map((s=>s.tag===t?{...s,value:e}:s));h(s);const i=s.reduce(((e,t)=>(t.value&&(e[t.tag]=t.value),e)),{});n({id:r,value:i})})(t,e.tag),disabled:p})})]},e.tag);var t}))})})),n("select",(({field:e,value:t,onChange:i})=>{const{class:n,disabled:a,help:r,label:o,labelPosition:c,multiple:d,id:p,options:u,variant:h}=e;return(0,l.jsx)(s.SelectControl,{className:n,disabled:a,help:r,label:o,labelPosition:c,onChange:e=>{i({id:p,value:e})},multiple:d,name:p,options:u,value:t,variant:h,__nextHasNoMarginBottom:!0})})),n("spacer",(({field:e})=>{const{height:t="20px"}=e;return(0,l.jsx)("div",{style:{height:t}})})),n("html",(({field:e,value:t,onChange:i})=>{const{class:n,std:a}=e,{baseControlProps:r,controlProps:o}=(0,s.useBaseControlProps)(e);return(0,l.jsx)(s.BaseControl,{...r,children:(0,l.jsx)("div",{dangerouslySetInnerHTML:{__html:a}})})})),n("tabs",(({field:e,value:i={},onChange:n,settings:r,settingsId:o})=>{const{tabs:c}=e,d=new URLSearchParams(window.location.search).get(e.id)||c[0].id,[p,u]=(0,t.useState)(d);return(0,t.useEffect)((()=>{const t=new URLSearchParams(window.location.search);t.set(e.id,p);const s=`${window.location.pathname}?${t.toString()}`;return window.history.replaceState(null,"",s),()=>{const t=new URLSearchParams(window.location.search);t.delete(e.id);const s=`${window.location.pathname}?${t.toString()}`;window.history.replaceState(null,"",s)}}),[p,e.id]),(0,l.jsx)("div",{className:"mtphrSettings__field--tabs__wrapper",children:(0,l.jsx)(s.TabPanel,{activeClass:"is-active",tabs:c.map((({id:e,label:t})=>({name:e,title:t}))),initialTabName:d,onSelect:e=>u(e),children:e=>{const t=c.find((({id:t})=>t===e.name));return(0,l.jsx)("div",{className:`mtphrSettings__field--tabs__content mtphrSettings__field--tabs__content--${e.name}`,children:t.fields.map((e=>(0,l.jsx)(a,{field:e,value:r[e.id]||"",onChange:n,settings:r,settingsId:o},e.id)))})}})})})),n("text",(({field:e,value:t,onChange:i})=>{const{class:n,disabled:a,help:r,label:o,labelPosition:c,id:d,placeholder:p,prefix:u,suffix:h,type:g="text"}=e;return(0,l.jsx)(s.__experimentalInputControl,{className:n,disabled:a,help:r,label:o,labelPosition:c,onChange:e=>{i({id:d,value:e})},placeholder:p,prefix:u,suffix:h,type:g,value:t})})),n("textarea",(({field:e,value:t,onChange:i})=>{const{class:n,disabled:a,help:r,label:o,labelPosition:c,id:d,placeholder:p,prefix:u,rows:h,suffix:g}=e;return(0,l.jsx)(s.TextareaControl,{className:n,disabled:a,help:r,label:o,labelPosition:c,onChange:e=>{i({id:d,value:e})},placeholder:p,prefix:u,rows:h,suffix:g,value:t})}));const{createRoot:f,render:v}=wp.element,w=document.getElementById("mtphr-settings-app");if(w){const e=w.getAttribute("namespace")?w.getAttribute("namespace"):"mtphr";console.log("settingsId",e),(e=>{if(!e||"string"!=typeof e)throw new Error("A valid namespace string must be provided.");window[e]=window[e]||{},window[e].registerComponent=n})(e),f?f(w).render((0,l.jsx)(r,{settingsId:e})):v((0,l.jsx)(r,{settingsId:e}),w)}})();