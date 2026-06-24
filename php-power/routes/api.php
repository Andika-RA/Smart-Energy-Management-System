Route::get(
'/power',
'PowerController@index'
);

Route::get(
'/forecast',
'ForecastController@index'
);

Route::get(
'/weather',
'WeatherController@index'
);