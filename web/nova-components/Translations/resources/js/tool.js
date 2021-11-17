Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'translations',
      path: '/translations',
      component: require('./components/Tool'),
    },
  ])
})
