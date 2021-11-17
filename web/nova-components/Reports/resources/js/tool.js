Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'reports',
      path: '/reports',
      component: require('./components/Tool'),
    },
  ])
})
