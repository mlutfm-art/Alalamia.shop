// NOTE: This patch updates di_container.dart to register SmartAds admin and fcm services and controllers.
// We will read the existing di_container and append registrations.

import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_admin_repository.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/fcm_repository.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_admin_service.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/fcm_service.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_admin_controller.dart';

// To apply these changes, the di_container.dart in the branch was updated to include the following registrations near the bottom:

/*
  sl.registerLazySingleton(() => AdAdminRepository(dioClient: sl()));
  sl.registerLazySingleton(() => FcmRepository(dioClient: sl()));
  sl.registerLazySingleton(() => AdAdminService(repo: sl()));
  sl.registerLazySingleton(() => FcmService(repo: sl()));
  sl.registerFactory(() => AdAdminController(service: sl()));
*/
