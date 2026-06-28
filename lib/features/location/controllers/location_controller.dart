import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_sixvalley_ecommerce/data/model/api_response.dart';
import 'package:flutter_sixvalley_ecommerce/features/location/domain/models/place_details_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/location/domain/models/prediction_model.dart';
import 'package:flutter_sixvalley_ecommerce/features/location/domain/services/location_service_interface.dart';
import 'package:flutter_sixvalley_ecommerce/main.dart';
import 'package:geocoding/geocoding.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';

class LocationController with ChangeNotifier {
  final LocationServiceInterface locationServiceInterface;
  LocationController({required this.locationServiceInterface});

  Position _position = Position(longitude: 0, latitude: 0, timestamp: DateTime.now(), accuracy: 1,
    altitude: 1, heading: 1, speed: 1, speedAccuracy: 1, altitudeAccuracy: 1, headingAccuracy: 1, );
  Position _pickPosition = Position(longitude: 0, latitude: 0, timestamp: DateTime.now(),
      accuracy: 1, altitude: 1, heading: 1, speed: 1, speedAccuracy: 1, altitudeAccuracy: 1, headingAccuracy: 1);
  
  bool _loading = false;
  bool get loading => _loading;
  
  bool _isBilling = true;
  bool get isBilling =>_isBilling;
  
  final TextEditingController _locationController = TextEditingController();
  TextEditingController get locationController => _locationController;

  Position get position => _position;
  Position get pickPosition => _pickPosition;
  Placemark _address = const Placemark();
  Placemark? _pickAddress = const Placemark();

  Placemark get address => _address;
  Placemark? get pickAddress => _pickAddress;

  bool _buttonDisabled = true;
  bool _changeAddress = true;
  GoogleMapController? _mapController;

  PredictionListModel? _predictionListModel;
  PredictionListModel? get predictionListModel => _predictionListModel;

  bool _updateAddAddressData = true;

  bool get buttonDisabled => _buttonDisabled;
  GoogleMapController? get mapController => _mapController;

  LatLng? _lastGeocodedLatLng;
  bool _isGeocoding = false;
  bool _isMapMoving = false;

  void setLocationController(String text) {
    _locationController.text = text;
  }

  void setMapController(GoogleMapController mapController) {
    _mapController = mapController;
  }

  void setMapMoving(bool moving) {
    _isMapMoving = moving;
  }

  void disposeMap() {
    _mapController = null;
    _lastGeocodedLatLng = null;
  }

  void getCurrentLocation(BuildContext context, bool fromAddress, {GoogleMapController? mapController}) async {
    _loading = true;
    notifyListeners();
    Position myPosition;
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      
      if (permission == LocationPermission.always || permission == LocationPermission.whileInUse) {
        Position newLocalData = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
        myPosition = newLocalData;
      } else {
        _loading = false;
        notifyListeners();
        return;
      }
    } catch(e) {
      myPosition = Position(
        latitude: 15.3694, 
        longitude: 44.1910,
        timestamp: DateTime.now(), accuracy: 1, altitude: 1, heading: 1, speed: 1, speedAccuracy: 1, altitudeAccuracy: 1, headingAccuracy: 1,
      );
    }

    if(fromAddress) {
      _position = myPosition;
    } else {
      _pickPosition = myPosition;
    }

    final controller = mapController ?? _mapController;
    if (controller != null) {
      controller.animateCamera(CameraUpdate.newCameraPosition(
        CameraPosition(target: LatLng(myPosition.latitude, myPosition.longitude), zoom: 17),
      ));
    }
    
    await _decodeLatLng(LatLng(myPosition.latitude, myPosition.longitude), fromAddress);
    
    _loading = false;
    notifyListeners();
  }

  /// Optimized Geocoding with smart caching and distance thresholding
  Future<void> _decodeLatLng(LatLng latLng, bool fromAddress) async {
    if (_isGeocoding) return;

    // Check if the coordinates are close enough to the last one (within 10 meters) to avoid redundant requests
    if (_lastGeocodedLatLng != null) {
       double distance = Geolocator.distanceBetween(
         _lastGeocodedLatLng!.latitude, _lastGeocodedLatLng!.longitude,
         latLng.latitude, latLng.longitude,
       );
       if (distance < 10) return; 
    }

    _isGeocoding = true;
    try {
      List<Placemark> placemarks = await placemarkFromCoordinates(
        latLng.latitude, 
        latLng.longitude,
      );

      if (placemarks.isNotEmpty) {
        Placemark place = placemarks[0];
        if (fromAddress) {
          _address = place;
          _locationController.text = placeMarkToAddress(place);
        } else {
          _pickAddress = place;
        }
        _lastGeocodedLatLng = latLng;
      }
    } catch (e) {
      debugPrint("Geocoding failed: $e");
      String fallbackAddress = await getAddressFromGeocode(latLng, Get.context!);
      if (fromAddress) {
        _address = Placemark(name: fallbackAddress);
        _locationController.text = fallbackAddress;
      } else {
        _pickAddress = Placemark(name: fallbackAddress);
      }
    } finally {
      _isGeocoding = false;
    }
  }

  void updateMapPosition(CameraPosition? position, bool fromAddress, String? address, BuildContext context) async {
    if(_updateAddAddressData && position != null) {
      try {
        if (fromAddress) {
          _position = Position(
            latitude: position.target.latitude, longitude: position.target.longitude, timestamp: DateTime.now(),
            heading: 1, accuracy: 1, altitude: 1, speedAccuracy: 1, speed: 1,altitudeAccuracy: 1, headingAccuracy: 1);
        } else {
          _pickPosition = Position(
            latitude: position.target.latitude, longitude: position.target.longitude, timestamp: DateTime.now(),
            heading: 1, accuracy: 1, altitude: 1, speedAccuracy: 1, speed: 1,altitudeAccuracy: 1, headingAccuracy: 1);
        }

        if (_changeAddress && !_isMapMoving) {
          // Trigger geocoding ONLY when map is stationary
          await _decodeLatLng(position.target, fromAddress);
          if(address != null) {
            _locationController.text = address;
          }
        } else {
          _changeAddress = true;
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
      }
      
      // Notify only when movement stops to keep UI smooth
      if (!_isMapMoving) {
        notifyListeners();
      }
    } else {
      _updateAddAddressData = true;
    }
  }

  void setLocation(String? placeID, String? address, GoogleMapController? mapController) async {
    _loading = true;
    notifyListeners();
    PlaceDetailsModel detail;
    ApiResponseModel response = await locationServiceInterface.getPlaceDetails(placeID);

    detail = PlaceDetailsModel.fromJson(response.response!.data);

    _pickPosition = Position(
      longitude: detail.location?.longitude ?? 0, latitude: detail.location?.latitude ?? 0,
      timestamp: DateTime.now(), accuracy: 1, altitude: 1, heading: 1, speed: 1,
        speedAccuracy: 1,altitudeAccuracy: 1, headingAccuracy: 1);

    _pickAddress = Placemark(name: address);
    _changeAddress = false;

    final controller = mapController ?? _mapController;
    if(controller != null) {
      controller.animateCamera(CameraUpdate.newCameraPosition(CameraPosition(target: LatLng(
          detail.location?.latitude ?? 0, detail.location?.longitude ?? 0), zoom: 16)));
    }
    _loading = false;
    notifyListeners();
  }

  void setAddAddressData() {
    _position = _pickPosition;
    _address = _pickAddress!;
    _locationController.text = placeMarkToAddress(_address);
    _updateAddAddressData = false;
    notifyListeners();
  }

  void setPickData() {
    _pickPosition = _position;
    _pickAddress = _address;
    _locationController.text = placeMarkToAddress(_address);
  }

  Future<String> getAddressFromGeocode(LatLng latLng, BuildContext context) async {
    ApiResponseModel response = await locationServiceInterface.getAddressFromGeocode(latLng);
    String address = '';
    if(response.response!.statusCode == 200 && response.response!.data['status'] == 'OK') {
      address = response.response!.data['results'][0]['formatted_address'].toString();
    }
    return address;
  }

  Future<List<Suggestions>> searchLocation(BuildContext context, String text) async {
    if(text.isNotEmpty) {
      _predictionListModel = null;
      ApiResponseModel response = await locationServiceInterface.searchLocation(text);
      if (response.response!.data is !List) {
        _predictionListModel =  PredictionListModel.fromJson(response.response!.data);
      }
    }
    return _predictionListModel?.suggestions ?? [];
  }

  /// Formats the Placemark into a professional address: Country, City, District, Street
  String placeMarkToAddress(Placemark placeMark) {
    List<String> parts = [];
    
    // Country
    if (placeMark.country != null && placeMark.country!.isNotEmpty) {
      parts.add(placeMark.country!);
    }
    
    // Administrative Area / City
    String? city = placeMark.administrativeArea ?? placeMark.locality ?? placeMark.subAdministrativeArea;
    if (city != null && city.isNotEmpty) {
      parts.add(city);
    }

    // SubLocality / District
    if (placeMark.subLocality != null && placeMark.subLocality!.isNotEmpty) {
      parts.add(placeMark.subLocality!);
    }

    // Street / Feature Name
    String? street = placeMark.thoroughfare ?? placeMark.street ?? placeMark.name;
    if (street != null && street.isNotEmpty && !parts.contains(street)) {
      parts.add(street);
    }

    return parts.join(', ');
  }

  void isBillingChanged(bool change) {
    _isBilling = change;
    if (change) {
      change = !_isBilling;
    }
    notifyListeners();
  }
}
