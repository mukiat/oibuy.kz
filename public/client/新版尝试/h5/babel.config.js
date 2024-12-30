const productPlugins = []

if(process.env.NODE_ENV === 'production'){
  productPlugins.push("transform-remove-console")
}

module.exports = {
  presets: ['@vue/app'],
  sourceType: 'unambiguous',
  "plugins": [
    [
      "import", {
        "libraryName": "vant",
        "libraryDirectory": "es",
        "style": true
      }
    ],
    [
      "component",
      {
        "libraryName": "element-ui",
        "styleLibraryName": "theme-chalk"
      },
    ],
    ...productPlugins
  ]
}
