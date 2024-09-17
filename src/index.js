/* global myCurrencyRates */

import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';

// React Component
const MyReactComponent = () => {
  const [prices, setPrices] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchPrices = () => {
    fetch(myCurrencyRates.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: new URLSearchParams({
        action: 'my_currency_rates_action',
        nonce: myCurrencyRates.nonce,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          setPrices(data.data);
        } else {
          setError(data.data);
        }
        setLoading(false);
      })
      .catch((error) => {
        console.error('Fetch Error:', error);
        setError(error.toString());
        setLoading(false);
      });
  };

  useEffect(() => {
    fetchPrices();
  }, []);

  if (loading) {
    return <p>Loading...</p>;
  }

  if (error) {
    return <p>Error: {error}</p>;
  }

  return (
    <div>
      <h2>Current Exchange Rates:</h2>
      <p>USD: {prices.usd ? prices.usd : 'N/A'}</p>
      <p>EUR: {prices.eur ? prices.eur : 'N/A'}</p>
      <p>Gold: {prices.gold ? prices.gold : 'N/A'}</p>
      <p>PLN: {prices.pln ? prices.pln : '1'}</p>
    </div>
  );
};

document.addEventListener('DOMContentLoaded', function () {
  const wrapElement = document.querySelector('.wrap');
  if (wrapElement) {
    const reactRoot = document.createElement('div');
    wrapElement.after(reactRoot);

    ReactDOM.render(<MyReactComponent />, reactRoot);
  }
});
