/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * */

( _ => $(document).ready( () => {
  if ('undefined' == typeof _.search) _.search = {};
  if ('undefined' == typeof _.search.address) {
    _.search.address = (request, response) => {
      _.post({
        url: _.url('{{route}}'),
        data: {
          action: 'search-properties',
          term: request.term

        },

      }).then(d => response('ack' == d.response ? d.data : []));

    };

  }

  if ('undefined' == typeof _.search.jobitems) {
    _.search.jobitems = (request, response) => {
      _.post({
        url: _.url('{{route}}'),
        data: {
          action: 'search-job-items',
          term: request.term

        },

      }).then(d => response('ack' == d.response ? d.data : []));

    };

  }

  _.catSort = cats => Object.entries(cats).sort((a, b) => String(a[1]).toUpperCase().localeCompare(String(b[1]).toUpperCase()));

}))( _brayworth_);
