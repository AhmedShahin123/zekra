Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'productprice',
      path: '/productprice',
      component: require('./components/Tool'),
    },
  ])
})
